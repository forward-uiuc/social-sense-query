<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Exceptions\QueryFailedToResolveException;
use App\Models\MetaQuery\MetaQueryNode;
use App\Models\MetaQuery\MetaQueryNodeDependency;
use App\Models\Query\QueryNode;
use Illuminate\Support\Collection;

class QueryResolver implements ResolvesMetaQueryNode
{
	private $node;
	private $status;
	private $queriesToResolve;
	
	public function __construct(MetaQueryNode $node) {
		$this->node = $node;

		if ($this->node->status === 'running') {
			throw new \Exception("Attempting to instantiate a resolver for a node that is already resolving");
		}

		if ($node->resolver_state) {
			$this->queriesToResolve = collect(json_decode($node->resolver_state));
		}
	}


	private function setNodeStatus(string $status) {
		if(!in_array($status, ['ready','running','waiting','error','paused'])){
			throw new \Exception('Attempting to set a status incorrectly');	
		}
		$this->node->status = $status;
		$this->node->save();	
	}



	public function resolve(): bool {
		
		if($this->node->status !== 'ready' && $this->node->status !== 'error') {
			throw new \Exception("Attempting to resolve a node that isn't ready");
		} else if ($this->nodeIsWaitingForDependencies()) {
			$this->setNodeStatus('waiting');
			throw new \Exception("Node is waiting for dependencies and is being told to resolve");
		}

		
		$this->setNodeStatus('running');
			
		$this->buildQueriesToResolve();
		$this->resolveQueries();
		$this->populateOutputValues();		

		return true;
	}


	
	private function resolveQueries() {
		$query = $this->node->node;
		$server = $query->server; // What server that these queries are going to go to
		$user = $query->user;

		
		foreach($this->queriesToResolve as $query) {
			if ($query->value == null) {
				$query->value =  json_decode($server->buildRequest($query->string, $user));
				
				// If this fails, throw an exception and let the job handle this problem
				if( $this->queryFailed($query->value)){
					$this->setNodeStatus('error');
					
					throw new \Exception(json_encode($query));
				}

				$this->node->resolver_state = json_encode($this->queriesToResolve);
				$this->node->save();
			}
		}
	}

	/*
	 * Check 2xx response from the server to see if the query failed to resolve
	 *  By convention, GraphQL will return json with a 'errors' property if the query
     *   fails to resolve. As such, we'll just check the response for that.
     */
	private function queryFailed($queryResponse): bool {
		return property_exists($queryResponse, 'errors'); 
																	
	}


	private function populateOutputValues() {
	}


	

	private function buildQueriesToResolve() {
		if($this->queriesToResolve) {
			return;
		}

		$query = $this->node->node;  // node is the morphTo property on the MetaQueryNode, returning here a Query type
		
		$queries = collect([$query->getQueryNode()]);
		$dependencies = $this->node->dependencies;

		if($this->node->dependencies->count() > 0) {
			// For each dependency, multiply the queries 
			// thus far by the values for each dependency
			while($dependency = $dependencies->shift()) {
				$queries = $queries->map(function($queryNode) use ($dependency) {
					return $this->applyValuesToQueryNode($dependency, $queryNode);
				})->flatten();
			}
		}

		$queryStrings = $queries->map(function($queryNode) {
			return (string) $queryNode;
		});	
	
	

		$this->queriesToResolve = $queryStrings->map(function($queryString){
			return (object) ['string' => $queryString, 'value' => null];
		});	
		

	}	

	private function applyValuesToQueryNode(MetaQueryNodeDependency $dependency, QueryNode $queryNode): Collection/* Of QueryNode */{
		$values = $dependency->output->values;
		if(gettype($values) != 'array') {
			throw new \Error("Error, the output of a node must be an array of values");
		}

		return collect($values)->map(function($value) use ($queryNode, $dependency){
			$newNode = clone $queryNode;
			$newNode->applyValue($dependency->input->path, $value);
			return $newNode;
		});
	}

	private function nodeIsWaitingForDependencies() {
		$isWaitingOnDependencies = $this->node->dependencies->map(function($dependency){
			return $dependency->output->value; // First, collect all the outputs
		})->reduce(function($carry, $value){
			return $carry || ($value == null); // Then compare them 
		}, false);
			
		return $isWaitingOnDependencies;
	}
	
	public function getStatus(): int {
		return ResolverStatus::READY;

		if($this->node->dependencies->count() == 0) {
			return ResolverStatus::READY;
		}

		$isWaitingOnDependencies = $this->node->dependencies->map(function($dependency){
			return $dependency->output->value; // First, collect all the outputs
		})->reduce(function($carry, $value){
			return $carry || ($value == null); // Then compare them 
		}, false);


		if($isWaitingOnDependencies) {
			return ResolverStatus::WAITING_FOR_DEPENDENCIES;
		} else {
			return ResolverStatus::READY;
		}
	}

	public function pause() {
		throw new \RuntimeError("Pause Not Yet Implemented");
	}

	public function resume() {
		throw new \RuntimeError("Reusme Not Yet Implemented");
	} 

	public function start() {
		throw new \RuntimeError("Start Not Yet Implemented");
	}


}

