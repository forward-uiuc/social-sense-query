<?php
namespace App\Models\MetaQuery\Functions;

class StringValue extends ValueFunction 
{
	public static function getName() : string {
		return 'String Value';
	}

	public static function getOutputs(): array {
		return ['value:String'];
	}

	public function evaluate($input): array {
		$val = $this->getStateValue();
		if($val== null) {
			throw new \Exception("Attempting to evaluate value function with no value in state");
		} 

		return [(string) $val];
	}

}
