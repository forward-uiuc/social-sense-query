<?php
namespace App\Models\MetaQuery\Functions;

class BoolValue extends ValueFunction 
{
	public static function getName() : string {
		return 'Boolean Value';
	}

	public static function getOutputs(): array {
		return ['value:Boolean'];
	}

	public function evaluate($input): array {
		return [(bool) $this->state->value];
	}
}
