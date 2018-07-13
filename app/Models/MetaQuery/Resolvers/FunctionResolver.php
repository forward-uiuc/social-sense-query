<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Models\MetaQuery\MetaQueryNode;
use App\Models\MetaQuery\MetaQueryNodeDependency;
use Illuminate\Support\Collection;

class FunctionResolver implements ResolvesMetaQueryNode
{
	public function __construct(MetaQueryNode $node) {
		$this->node = $node;
	}

	public function resolve(): bool {
		if($this->getStatus() != ResolverStatus::READY) {
			return false;
		}


		if($dependencies->count() > 0) {
			dd("TRUE?");	
			// For each dependency, multiply the queries 
			// thus far by the values for each dependency
			while($dependency = $dependencies->shift()) {
				$queries = $queries->map(function($queryNode) use ($dependency) {
					return $this->applyValuesToQueryNode($dependency, $queryNode);
				})->flatten();
			}
			
		dd($queries->map(function($query) {
			return (string) $query;	
			}));	

		}


		$values = $queries->map(function($queryNode) use ($server, $user){
			return $server->buildRequest((string) $queryNode, $user);
		}); 
	
		return true;
	}
	
	
	public function getStatus(): int {
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

