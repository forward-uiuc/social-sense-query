<?php
namespace App\Models\MetaQuery\Functions;

class Sum extends AbstractFunction 
{
	public static function getName() : string {
		return 'Sum';
	}

	public static function getInputs(): array {
		return ['values:Num'];
	}

	public static function getOutputs(): array {
		return ['sum:Num'];
	}

	public function evaluate($input): array {
		$vals = collect($input->values)->map(function($num){
			return (float) $num;
		});

		$sum = $vals->reduce(function($carry, $value) {
			return $carry + $value;
		}, 0);
		return [$sum];
	}
}
