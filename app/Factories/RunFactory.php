<?php

namespace App\Factories;

use App\Models\MetaQuery\Stage;
use App\Models\MetaQuery\Run;
use App\Models\MetaQuery\MetaQuery;
use App\Models\MetaQuery\MetaQueryNode;
use App\Models\MetaQuery\MetaQueryNodeInput;
use App\Models\MetaQuery\MetaQueryNodeOutput;
use App\Models\MetaQuery\MetaQueryNodeDependency;

class RunFactory {

	public function createRun(MetaQuery $metaQuery) {
		$run = new Run();
		$run->topology = $metaQuery->topology;
		$metaQuery->runs()->save($run);

		$queryStructure = json_decode($run->topology);
		// first, create each stage and its nodes
		$this->generateRunStages($queryStructure, $run);	
		// last, mark all nodes in the first stage as ready to execute
		$run->stages->first()->nodes->each(function($node){
			$node->status = 'ready';
			$node->save();
		});

		return $run;
	}


	/*
	 * Given an array of topology nodes and a run, generate the 
	 * stages and meta query nodes at each stage accordingly
	 */
	private function generateRunStages($queryStructure, Run $run) {
		$stages = collect([]);

		$nodes = collect($queryStructure->nodes);
		$stage0 = new Stage();
		$run->stages()->save($stage0);


		// find all the nodes that have no dependencies
		$stage0Nodes = $nodes->filter(function($node) {
			return count($node->dependencies) == 0;
		});

		// Create all the meta query nodes
		$metaQueryNodes = $stage0Nodes->map(function($nodeStructure) use ($stage0){
			return $this->createNodeFromStructure($nodeStructure, $stage0);
		});

		$addedNodes = $metaQueryNodes;

		// for all subsequent nodes
		while($addedNodes->count() != $nodes->count()) {

			//create a stage
			$stageN = new Stage();
			$run->stages()->save($stageN);

			// get all of the topology_id's from the nodes that we've added
			$addedNodesTopologyIDs = $addedNodes->pluck('topology_id');

			// identify the nodes that we still need to add
			$nodesThatNeedAdded = $nodes->filter(function($nodeStructure) use ($addedNodesTopologyIDs) {
				return !$addedNodesTopologyIDs->contains($nodeStructure->topology_id);
			});

			// from the ones that need added, identify the nodes we can add
			$nodesToAdd = $nodesThatNeedAdded->filter(function($nodeStructure) use ($addedNodesTopologyIDs){
				// get the output_topology_node_id's from our dependencies
				$dependencyIDs = collect($nodeStructure->dependencies)->pluck('output_topology_node_id');

				// see if that node id hasn't been made
				$dependencyNotMade = $dependencyIDs->filter(function($dependencyID) use($addedNodesTopologyIDs){
					return !$addedNodesTopologyIDs->contains($dependencyID);
				})->count() > 0;
				
				// return the situation that the node _has_ been made
				return !$dependencyNotMade;
			});

			$nodesToAdd->each(function($nodeStructure) use ($stageN, $addedNodes) {
				$newNode = $this->createNodeFromStructure($nodeStructure, $stageN);	 // add the node to the stage
				$addedNodes->push($newNode);
				//create that node's dependencies
				collect($nodeStructure->dependencies)->each(function($dependencyStructure) use($newNode, $addedNodes){
					$dependency = new MetaQueryNodeDependency();
				
						

					$input = $newNode->inputs->where('path', $dependencyStructure->input_path)->first();
					$output = $addedNodes
								->where('topology_id', $dependencyStructure->output_topology_node_id)->first()
								->outputs()->where('path', $dependencyStructure->output_path)->first();

					$newNode->dependencies()->save(new MetaQueryNodeDependency([
						'meta_query_node_input_id' => $input->id, 
						'meta_query_node_output_id' => $output->id
					]));
				});
				
			});
		}
	}


	private function createNodeFromStructure($nodeStructure, $stage) {
		$metaQueryNode = new MetaQueryNode([
			'topology_id' => $nodeStructure->topology_id,
			'node_type' => $nodeStructure->type_name,
			'node_id' => $nodeStructure->type_id,
			'resolved' => false,
			'node_state' => json_encode($nodeStructure->state)
		]);

		$stage->nodes()->save($metaQueryNode);

		$inputs = collect($nodeStructure->inputs)->map(function($inputPath) {
			return new MetaQueryNodeInput(['path' => $inputPath]);
		});

		$outputs = collect($nodeStructure->outputs)->map(function($outputPath) {
			return new MetaQueryNodeOutput(['path' => $outputPath]);
		});


		$metaQueryNode->outputs()->saveMany($outputs);
		$metaQueryNode->inputs()->saveMany($inputs);
		return $metaQueryNode;
	}
}
