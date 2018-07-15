<?php

namespace App\Models\MetaQuery\Resolvers;

use App\Models\MetaQuery\MetaQueryNode;
use App\Models\MetaQuery\MetaQueryNodeDependency;
use Illuminate\Support\Collection;

class FunctionResolver implements ResolvesMetaQueryNode
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
		$dependencies = $this->node->dependencies;
		
		// retrieve all the values
		$values = $dependencies->map(function($dependency) {
			return $dependency->output->value;
		})->collapse();
		
		$output = $this->node->outputs->first();
		// compute the output value
		$output->value = json_encode($this->node->node->computeOutput("", $values->toArray()));
		$output->save();

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

