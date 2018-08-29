<?php
namespace App\Models\MetaQuery\Functions;

class SplitByWhiteSpace extends AbstractFunction 
{
	public static function getName() : string {
		return 'Split strings by white space';
	}

	public static function getInputs(): array {
		return ['sentences:String'];
	}

	public static function getOutputs(): array {
		return ['words:String'];
	}

	public function evaluate($input): array {
		return collect($input->sentences)->map(function($sentence) {
			return preg_split('/\s+/', $sentence);
		})->collapse()->toArray();
	}
}
