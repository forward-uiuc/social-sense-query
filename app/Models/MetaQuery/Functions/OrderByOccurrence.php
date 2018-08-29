<?php
namespace App\Models\MetaQuery\Functions;

class OrderByOccurrence extends AbstractFunction 
{
	public static function getName() : string {
		return 'OrderByOccurence';
	}

	public static function getInputs(): array {
		return ['values:Any'];
	}

	public static function getOutputs(): array {
		return ['valuesSortedByOccurence:Any'];
	}

	public function evaluate($input): array {
		return collect($input->values)->groupBy(function($value, $index){ 
			return $value;
		})->sortByDesc(function($collection){
			return $collection->count();
		})->collapse()->toArray();
	}
}
