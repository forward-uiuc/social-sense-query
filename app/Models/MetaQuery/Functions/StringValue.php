<?php
namespace App\Models\MetaQuery\Functions;

class StringValue extends ValueFunction 
{
	public static function getName() : string {
		return 'String Value';
	}

	public static function getOutputs(): array {
		return ['value:String'];
	}

	public function evaluate($input): array {
		return [(string) $this->state->value];
	}

}
