<?php
namespace App\Models\MetaQuery\Functions;

class Min extends AbstractFunction 
{
	public static function getName() : string {
		return 'Min';
	}

	public static function getInputs(): array {
		return ['values:Num'];
	}

	public static function getOutputs(): array {
		return ['min:Num'];
	}

	public function evaluate($input): array {
		return [collect($input->values)->map(function($val){
			return (float) $val;
		})->min()];
	}
}
