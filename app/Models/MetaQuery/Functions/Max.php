<?php
namespace App\Models\MetaQuery\Functions;

class Max extends AbstractFunction 
{
	public static function getName() : string {
		return 'Max';
	}

	public static function getInputs(): array {
		return ['values:Num'];
	}

	public static function getOutputs(): array {
		return ['max:Num'];
	}

	public function evaluate($input): array {
		return [collect($input->values)->map(function($val){
			return (float) $val;
		})->max()];
	}
}
