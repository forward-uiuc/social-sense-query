<?php
namespace App\Models\MetaQuery\Functions;

class IntValue extends ValueFunction 
{
	public static function getName() : string {
		return 'Int Value';
	}

	public static function getOutputs(): array {
		return ['value:Int'];
	}

	public function evaluate($input): array {
		return [(int) $this->state->value];
	}

}
