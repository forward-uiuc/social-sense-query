<?php
namespace App\Models\MetaQuery\Functions;

class Average extends AbstractFunction 
{
	public static function getName() : string {
		return 'Average';
	}

	public static function getInputs(): array {
		return ['values:Num'];
	}

	public static function getOutputs(): array {
		return ['average:Float'];
	}

	public function evaluate($input): array {
		$vals = collect($input->values)->map(function($value){
			return (float) $value;
		});

		$sum = $vals->reduce(function($carry, $value) {
			return $carry + $value;
		}, 0);

		return [$sum / $vals->count()];
	}
}
