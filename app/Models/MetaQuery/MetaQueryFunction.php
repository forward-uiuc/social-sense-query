<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class MetaQueryFunction extends Model
{
	protected $fillable = ['name'];

	/*
	 * Get what MetaQueryNodes this function is part of
	 */
	public function nodes() {
		return $this->morphMany(MetaQueryNode::class, 'node');
	}


	public function computeOutput(string $path, $values) {

		// @TODO: do something with the $path :P
		$result;

		switch($this->name) {
			case "Count":
				$result = $values->count();
			break;	
			
			case "Max Ints":
			case "Max Floats":
				$result = $values->max();
				break;

			case "Min Ints":
			case "Min Floats":
				$result = $values->min();
				break;

			case "Sum Ints":
			case "Sum Floats":
				$result = $values->reduce(function($carry, $value) {
					return $carry + $value;
				},0);
				break;

			case "Average Ints":
			case "Average Floats":
				$sum = $values->reduce(function($carry, $value) {
					return $carry + $value;	
				}, 0);

				$result = ((float) $sum) / $values->count();
				break;
		}	
		return $result;
	}
}
