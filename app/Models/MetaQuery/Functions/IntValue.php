<?php
namespace App\Models\MetaQuery\Functions;

class IntValue extends ValueFunction 
{
	public static function getName() : string {
		return 'Int Value';
	}

	public static function getOutputs(): array {
		return ['value:Int'];
	}

	public function evaluate($input): array {
		$val = $this->state->value != null ? $this->state->value : $this->state->control->value;
		if($val== null) {
			throw new \Exception("Attempting to evaluate value function with no value in state");
		} 
		return [(int) $val];
	}

}
