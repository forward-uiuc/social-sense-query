<?php
namespace App\Models\MetaQuery\Functions;

// A function that simply returns a value
class LimitFunction extends AbstractFunction 
{
	public static function getName(): string {
		return 'Limit';
	}

	public static function getInputs(): array {
		return ['k:Int', 'values:Any'];
	}

	public static function getOutputs(): array {
		return ['kValues:Any'];
	}

	public function evaluate($input): array {
		$k = (int) $input->k[0];
		return collect($input->values)->take($k)->toArray();
	}
}
