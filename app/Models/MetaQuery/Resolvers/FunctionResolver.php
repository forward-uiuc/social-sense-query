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
			$varName = explode(':', $dependency->input->path)[0];	
			return [$varName => json_decode($dependency->output->value)];
		});

		// Assemble the value object for the function
		$values = (object) $values->collapse()->toArray();

		
		$output = $this->node->outputs->first();
		// compute the output value
		$function = app()->make('App\Repositories\Contracts\MetaQueryFunctionRepositoryInterface')->findById($this->node->node_id);
		$instance = new $function->className(json_decode($this->node->node_state));

		$calculatedValue = $instance->evaluate($values);

		// If the calculated value increases the dimension
		if(count($calculatedValue) > 0 && gettype($calculatedValue[0]) === 'array') {
			   $calculatedValue = collect($calculatedValue)->collapse()->toArray();
		} else if (gettype($calculatedValue)  !== 'array'){
			   $calculatedValue = [$calculatedValue];
		}

		$output->value = json_encode($calculatedValue);
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

