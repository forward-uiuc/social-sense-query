<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetaQuery extends Model
{

	protected $fillable = ['schedule','canvas','topology'];
	protected $table = 'meta_queries';

	/*
	 * Get the user this MetaQuery belongs to
	 */
	public function user() {
		return $this->belongsTo('App\User');
	}


	/*
	 * Retrieve all the nodes for this 
	 * meta query
	 */
	public function queries() {
		return $this->hasMany('App\Query');
	}

	/*
	 * Submit this meta query
	 */
	public function submit() {
		$queryNodes = collect(json_decode($this->topology))->map(function($topologyNode){
			return new MetaQueryNode($topologyNode);
		})->sort(function($node) {
			return count($node->dependencies);	
		});


		// Maintain a collection of 
		// queries that reflect how we resolved this at each level
		$resolutionOrder = collect([]);
		$done = false;

		while(!$done) {

			// First, find all the nodes that can be resolved
			// This depends on applying a node removing a node's id from it's dependencies
			// (meaning, a node can be resolved if it has 0 dependencies)
			$toResolve = $queryNodes->filter(function($node) {
				return ($node->resolved == false && count($node->dependencies) == 0);
			});

			// Second, resolve all said nodes
			$toResolve->each(function($node) {
				$node->resolve($this->user->authorizations);
			});
			$resolved = $toResolve;

			$resolutionOrder->push($resolved);

			// Third
			// Find all the nodes in our queryNodes that have a resolved node's id as a dependency
			$resolvedIDs = $resolved->map(function($resolvedNode) {
				return $resolvedNode->id;
			})->toArray();

			$toApplyArguments = $queryNodes->filter(function($node) use ($resolvedIDs) {

				$dependencyIDs = collect($node->dependencies)->map(function($dependency) {
					return $dependency->id;
				})->toArray();
				$intersection = array_intersect($dependencyIDs, $resolvedIDs);

				return !$node->resolved && count($intersection) > 0;
			});


				
			// Apply all resolved nodes to all nodes that have at least one of those nodes as a dependency
			$toApplyArguments->each(function($nodeRequiringData) use ($resolved) {
				
				$resolved->each(function($resolvedNode) use ($nodeRequiringData) {
					$nodeRequiringData->apply($resolvedNode);
				});
			});

			// We're done if we've resolved all nodes
			$done = $queryNodes->filter(function($node) {
				return $node->resolved;
			})->count() == $queryNodes->count();
		}

		
		$data = $resolutionOrder->map(function($stage) {
			$stageData = $stage->map(function($node) {
				dump($node->data);
				return (object) ['queries' => $node->getQueryStrings(), 'data' => $node->data];
			});

			return $stageData;
		});
	}
}
