<?php
namespace App\Models\MetaQuery\Functions;

// A function that simply returns a value
abstract class ValueFunction extends AbstractFunction
{
	public static function getInputs(): array {
		return [];
	}

	protected function getStateValue() {
		if(property_exists($this->state, 'value')) {
			return $this->state->value;
		} else if(property_exists($this->state->control, 'value')){
			return $this->state->control->value;	
		} else {
			return null;
		}	
	}
}
