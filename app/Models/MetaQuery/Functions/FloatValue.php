<?php
namespace App\Models\MetaQuery\Functions;

class FloatValue extends ValueFunction 
{
	public static function getName() : string {
		return 'Float Value';
	}

	public static function getOutputs(): array {
		return ['value:Float'];
	}

	public function evaluate($input): array {
		return [(float) $this->state->value];
	}

}
