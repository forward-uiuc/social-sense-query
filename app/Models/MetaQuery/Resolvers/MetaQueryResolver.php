<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Models\MetaQuery\MetaQueryNode;
use App\Models\MetaQuery\MetaQueryNodeDependency;
use App\Factories\RunFactory;
use Illuminate\Support\Collection;

class MetaQueryResolver implements ResolvesMetaQueryNode
{
	public function __construct(MetaQueryNode $node) {
		$this->node = $node;

		if ($this->node->status === 'running') {
			throw new \Exception("Attempting to instantiate a resolver for a node that is already resolving");
		}
	}

	private function setNodeStatus(string $status) {
		if(!in_array($status, ['ready','running','waiting','error','paused', 'completed'])){
			throw new \Exception('Attempting to set a status incorrectly');	
		}
		$this->node->status = $status;
		$this->node->save();	
	}

	public function resolve(): bool {
		// First, create the run for this metea query
		$runFactory = new RunFactory();
		$run = $runFactory->createRun($this->node->node);

		// Next, write the values of the inputs
		// of this meta query to their corresponding
		// inputs
		$runNodes = $run->stages->map(function($stage) {
			return $stage->nodes;
		})->collapse();

		$dependencies = $this->node->dependencies;

		$dependencies->each(function($dependency) use ($runNodes) {
			$input = explode('.', $dependency->input->path);

			$nodeName = array_shift($input);
			$nodeTopologyId = array_shift($input);
			$nodeInputPath = implode('.', $input);
			

			// Find the node
			$subNode = $runNodes->first(function($node) use ($nodeTopologyId){
				return $node->topology_id == $nodeTopologyId;
			});


			// assign the value to its input
			$input = $subNode->inputs->first(function($input) use ($nodeInputPath) {
				return $input->path === $nodeInputPath;
			});

			// Create the dependency
			$subNode->dependencies()->save(new MetaQueryNodeDependency([
				'meta_query_node_input_id' => $input->id,
				'meta_query_node_output_id' => $dependency->output->id
			]));
		});

		// Next, resolve the nodes in this meta query
		$run->resolve();		

		// Last, save the outputs to this ndoe
		$outputs = $this->node->outputs;

		$outputs->each(function($output) use ($runNodes){
			$path = explode('.', $output->path);

			$nodeName = array_shift($path);
			$nodeTopologyId = array_shift($path);
			$nodeOutputPath = implode('.', $path);
			

			$subNode = $runNodes->first(function($node) use ($nodeTopologyId) {
				return $node->topology_id == $nodeTopologyId;
			});

			$subOutput = $subNode->outputs->first(function($output) use ($nodeOutputPath) {
				return $output->path === $nodeOutputPath;
			});
			$output->value = $subOutput->value;
			$output->save();
		});

		$this->setNodeStatus('completed');
		return true;
	}
	
	
	public function pause() {
		throw new \RuntimeError("Pause Not Yet Implemented");
	}

	public function resume() {
		throw new \RuntimeError("Reusme Not Yet Implemented");
	} 
}

