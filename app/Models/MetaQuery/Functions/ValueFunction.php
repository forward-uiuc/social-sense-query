<?php
namespace App\Models\MetaQuery\Functions;

// A function that simply returns a value
abstract class ValueFunction extends AbstractFunction
{
	public static function getInputs(): array {
		return [];
	}
}
