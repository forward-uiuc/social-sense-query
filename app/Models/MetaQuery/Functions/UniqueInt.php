<?php

namespace App\Models\MetaQuery\Functions;

class UniqueInt extends AbstractFunction {
	public static function getName() : string {
		return 'Unique Ints';
	}
	
	public static function getInputs(): array {
		return ['values:Int'];
	}

	public function evaluate($input): array {
		return collect($input->values)->map(function($val){
			return (int) $val;
		})->unique()->values()->toArray();	
	}

	public static function getOutputs(): array {
		return ['uniqueInts:Int'];
	}
}
