<?php

namespace App\Models\MetaQuery\Functions;

class UniqueString extends AbstractFunction {
	public static function getName() : string {
		return 'Unique Strings';
	}
	
	public static function getInputs(): array {
		return ['values:String'];
	}

	public function evaluate($input): array {
		return collect($input->values)->map(function($val){
			return (string) $val;
		})->unique()->values()->toArray();	
	}

	public static function getOutputs(): array {
		return ['uniqueStrings:String'];
	}
}
