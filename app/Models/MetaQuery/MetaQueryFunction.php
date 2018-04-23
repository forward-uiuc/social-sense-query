<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Support\array;


class MetaQueryFunction extends Model
{
	protected $fillable = ['name'];

	/*
	 * Get what MetaQueryNodes this function is part of
	 */
	public function nodes() {
		return $this->morphMany(MetaQueryNode::class, 'node');
	}


	/*
	 * Input: an array of values or array of arrays of values to this path
	 * Output: an array of values?
	 */
	public function computeOutput(string $path, array $values) {

		// @TODO: do something with the $path :P
		if(collect($values)->count() == 0) {
			return $values;
		}

		if(preg_match('/Merge.*/', $this->name)){
		}
		$mapFunction = gettype(collect($values)->first()) === "array";

		$f = function(array $v) {
			return $v;
		};

		switch($this->name) {
			case "Count":
				$f = function(array $v) {
					return collect($v)->count();	
				};
			break;	
			
			case (preg_match('/Max.*/', $this->name) ? true : false):
				$f = function(array $v) {
					return collect($v)->max();	
				};
				break;

			case (preg_match('/Min.*/', $this->name) ? true : false):
				$f = function(array $v) {
					return collect($v)->min();	
				};
				break;

			case (preg_match('/Sum.*/', $this->name) ? true : false):
				$f = function(array $v) {
					return collect($v)->reduce(function($carry, $value) {
						return $carry + $value;
					},0);
				};
				break;

			case (preg_match('/Average.*/', $this->name) ? true : false):
				$f = function(array $v){
					$vals = collect($v);
					return $vals->reduce(function($carry, $value){
						return $carry + $value;	
					}, 0);

					return array(((float) $sum) / $vals->count());
				};
				break;

			case (preg_match('/Limit.*/', $this->name) ? true : false):
				$limit = (int) explode(' ', $this->name)[1];

				$f = function(array $v) use($limit) {
					return collect($v)->take($limit)->toArray();
				};

				break;

			case (preg_match('/Unique.*/', $this->name) ? true : false):

				$f = function(array $v){
						return  collect($v)->unique()->toArray();
				};

				break;

			case (preg_match('/Split white space.*/', $this->name) ? true: false):
				$f = function(array $v) {
					return collect($v)->map(function($value) {
						return preg_split('/\s+/', $value);
					})->toArray();
				};
				break;

			case (preg_match('/Merge.*/', $this->name) ? true : false):
				if($mapFunction) {
					return collect($values)->collapse()->toArray();
				} else {
					return $values;
				}
			break;

			case (preg_match('/Remove stop words.*/', $this->name) ? true : false):
				$stopWords = json_decode($this->state);

				$f = function(array $v) use ($stopWords) {

					return collect($v)->filter(function($value) use ($stopWords){
						return !in_array($value, $stopWords);
					})->toArray();
					

				};

				break;

			case (preg_match('/Order by most frequent.*/', $this->name) ? true : false):

				$f = function(array $v) {
					$vals = collect($v);

					return $vals->groupBy(function($value, $index){
						return $value;
					})->sortByDesc(function($collection){
						return $collection->count();
					})->collapse()->toArray();

				};
				break;


			case (preg_match('/Convert to lower case.*/', $this->name) ? true : false):

				$f = function(array $v) {
					$vals = collect($v);

					return $vals->map(function($value) {
						return strtolower($value);
					})->toArray();
				};
				break;
		}	

		

		if($mapFunction) {
			return collect($values)->map(function($v) use ($f, $values) {

				if(gettype($f($values)) === "array"){
					return array_values($f($v));

				} else if (gettype($f($values)) != "object"){
					return $f($v);

				} else {
					dd("This is an error message", $this->name, $values, $f($v));
				}

			})->toArray();

		} else {

				if(gettype($f($values)) === "array"){
					return array_values($f($values));

				} else if (gettype($f($values)) != "object"){

					return $f($values);
				} else {
					dd("This is an error message", $this->name, $values, $f($values));
				}

			return array_values($f($values));
		}

	}
}
