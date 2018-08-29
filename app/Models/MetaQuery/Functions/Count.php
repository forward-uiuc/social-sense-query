<?php
namespace App\Models\MetaQuery\Functions;

class Count extends AbstractFunction 
{
	public static function getName() : string {
		return 'Count';
	}

	public static function getInputs(): array {
		return ['values:Any'];
	}

	public static function getOutputs(): array {
		return ['count:Int'];
	}

	public function evaluate($input): array {
		return [collect($input->values)->count()];
	}
}
