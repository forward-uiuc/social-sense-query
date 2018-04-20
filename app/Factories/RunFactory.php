<?php

namespace App\Factories;

use App\Models\MetaQuery;

class RunFactory {

	public function createRun(MetaQuery\MetaQuery $metaQuery) {
		$run = new MetaQuery\Run();
		$run->topology = $metaQuery->topology;
		$metaQuery->runs()->save($run);

		$nodes = collect(json_decode($run->topology));
		
		// first, create each stage and its nodes
		$this->generateRunStages($nodes, $run);	

		// second, create all the dependencies that the nodes have
		$this->createDependencies($run, $nodes);

		// last, for each node stage, resolve said node
		$run->stages->each(function($stage) {
			$stage->nodes->each(function($node) {
				$node->resolve();
			});
		});

		return $run;
	}


	private function createDependencies(MetaQuery\Run $run, $topologyNodes) {

		/*
		 * First, lets make a collection of objects
		 * with an 'id' equal to a topology id and inputs array
		 * equal to that node's inputs
		 */
		$inputDependencies = $topologyNodes->filter(function($node) {

			// First, get all the nodes with some input
			return collect($node->inputs)->filter(function($input) {
				$path = key($input);
				return $input->$path != null;
			})->count() > 0;

			// Second, return those inputs
		})->map(function($nodeWithDependency) {
			$inputs = collect($nodeWithDependency->inputs)->filter(function($input) {
				$path = key($input);
				return $input->$path != null;
			});
			return (object) ['id' => $nodeWithDependency->id->topology, 'inputs' => $inputs];
		});

		$runNodes = $run->stages->map(function($stage) {
			return $stage->nodes;
		})->collapse();

		$dependentNodes = $runNodes->whereIn('topology_id', $inputDependencies->pluck('id'));	


		/*
		 * For each node that has dependencies
		 */
		$dependentNodes->each(function($node) use ($inputDependencies, $runNodes) {

			// first, grab the dependencies that $node has 
			$nodeDependencies = $inputDependencies->first(function($topologyDependency) use ($node) {
				return $topologyDependency->id == $node->topology_id;
			});

			/*
			 * Foreach dependency that $node has 
			 */
			$nodeDependencies->inputs->each(function($input) use ($node, $runNodes) {
				$path = key($input);  // $path is the path of the input to this node
				$inputID = $node->inputs()->wherePath($path)->first()->id;
				
				$outputNode = $runNodes->where('topology_id', $input->$path->topology_id)->first();
				$outputID = $outputNode->outputs()->where('path', $input->$path->path)->first()->id;
				
				$dependency = new MetaQuery\MetaQueryNodeDependency();
				$dependency->meta_query_node_input_id = $inputID;
				$dependency->meta_query_node_output_id = $outputID;
				$node->dependencies()->save($dependency);
			});
		});
	}


	/*
	 * Given an array of topology nodes and a run, generate the 
	 * stages and meta query nodes at each stage accordingly
	 *
	 */
	private function generateRunStages($nodes, MetaQuery\Run $run) {
		$stages = collect([]);

		// First, collect all the nodes that have no input dependencies
		// and return a set of id's 
		$nodeIds = $nodes->filter(function($node) {
			return collect($node->inputs)->filter(function($input) {
				$path = key($input);
				return $input->$path != null;
			})->count() == 0;
		})->map(function($node){
			return $node->id->topology;	
		});
		
		$stages->push($nodes->whereIn('id.topology', $nodeIds));
		$remaining = $nodes->whereNotIn('id.topology', $nodeIds);
		
		/*
		 * Next, iteratively filter the nodes based off of
		 * which nodes can be logically resolved
		 */
		while($remaining->count() > 0){

			$stageNodes = $remaining->filter(function($node) use ($nodeIds) {

				// First, collect all of the id's that this node is dependant on
				$dependentIds = collect($node->inputs)->filter(function($input) {
					$path = key($input);
					return $input->$path != null;
				})->map(function($input){
					$path = key($input);
					return $input->$path->topology_id;
				});

				// Next, return whether this node's dependencies is within
				return $dependentIds->diff($nodeIds)->count() == 0;
			});

			$stages->push($stageNodes);
			$stageNodes->each(function($node) use ($nodeIds) {
				$nodeIds->push($node->id->topology);
			});

			$remaining = $nodes->whereNotIn('id.topology', $nodeIds);
		}
		
		// Nice! Now at this point, stages is a collection of node collections.
		// Lets change that by mapping a function to each collection to create an
		// App\Models\MetaQuery\Stage and an App\Models\MetaQuery\MetaQueryNode
		// for each node in that stage
		$stages->map(function($nodesInStage) use ($run) {
			$stage = new MetaQuery\Stage();
			$run->stages()->save($stage);
			
			$stage->save();

			$nodesInStage->each(function($topologyNode) use ($stage) {
				$stage->nodes()->save( MetaQuery\MetaQueryNode::fromTopologyNode($stage, $topologyNode)); 
			});

			return $stage;
		});	

		return $stages;
	}
}
