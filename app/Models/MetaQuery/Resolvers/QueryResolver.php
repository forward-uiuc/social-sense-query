<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Models\Query\QueryHistory;
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



	public function resolve(): bool {
		
		if($this->node->status !== 'ready' && $this->node->status !== 'error') {
			dd($this->node->toJson());
			throw new \Exception("Attempting to resolve a node that isn't ready");
		} else if ($this->nodeIsWaitingForDependencies()) {
			$this->node->setStatus('waiting');
			throw new \Exception("Node is waiting for dependencies and is being told to resolve");
		}

		
		$this->node->setStatus('running');
			
		$this->buildQueriesToResolve();
		$this->resolveQueries();
		$this->populateOutputValues();		
		
		$this->node->setStatus('completed');
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
					$this->node->setStatus('error');
					
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
		$outputs = $this->node->outputs; 

		$values = $this->queriesToResolve->map(function($result) {
			return $result->value;
		});

		$outputs->each(function($output) use ($values){
		   $allOutputValues = $values->map(function($value) use ($output){
				   return $this->getOutput($output->path, $value)->toArray();
		   })->collapse()->toArray();

			while(count($allOutputValues) > 0 && gettype($allOutputValues[0] === 'array')) {
				$allOutputValues = $allOutputValues->collapse()->toArray();
			}

			$output->value = json_encode($allOutputValues);
			$output->save();
		});


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
		$values = json_decode($dependency->output->value);

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
	
	public function pause() {
		throw new \RuntimeError("Pause Not Yet Implemented");
	}

	public function resume() {
		throw new \RuntimeError("Reusme Not Yet Implemented");
	} 



	/*
	 *  Given a query path, get the out value 
	 */
	public function getOutput($path, $response) {

		// Example of a $path:
		// query.reddit.searchSubredditNames:String*
		// so what we do is get all the elements and remove the 'query' part
		$attributes = explode('.', $path);
		array_shift($attributes);

		$data = collect([$response->data]);


		// for each ['query', 'tweet*', 'user', 'timeline*','id']
		foreach($attributes as $index => $attribute) {
				// First, remove the '*' from the string as we can just test the response if it's a list

				$attribute = str_replace('*','', $attribute);

				// If it's the last attribute, remove the ':Type' from the end
				if($index == count($attributes) - 1) {
						$attribute = explode(':', $attribute)[0];
				}

				$isList = gettype($data->first()) == 'array';
				$data = $data->map(function($responseField) use ($isList, $attribute){
						if($isList) {

								return collect($responseField)->map(function($element) use ($attribute){
										return $element->$attribute;
								});

						} else {
								return $responseField->$attribute;
						}
				});

				if($isList) {
						$data = $data->collapse();
				}

		}

		return $data;
	}

	private static function getEndOfString($string) {
		return substr($string, strlen($string)-1, strlen($string));
	}
}
