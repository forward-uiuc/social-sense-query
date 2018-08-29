<?php

namespace App\Models\MetaQuery\Functions;

class UniqueFloat extends AbstractFunction {
	public static function getName() : string {
		return 'Unique Floats';
	}
	
	public static function getInputs(): array {
		return ['values:Float'];
	}

	public function evaluate($input): array {
		return collect($input->values)->map(function($val){
			return (float) $val;
		})->unique()->values()->toArray();	
	}

	public static function getOutputs(): array {
		return ['uniqueFloats:Float'];
	}
}
