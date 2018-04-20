<?php

namespace App\Models\Query;

use Illuminate\Database\Eloquent\Model;

use Query\QueryNode;
use App\Models\User;

class QueryHistory extends Model
{
	protected $table = 'query_histories';
	protected $fillable = ['duration','data', 'has_error', 'query_structure'];
	protected $appends = ['size']; 

	/*
	 * Get the query that generated this history item
	 */
	public function queryOfRecord() {
		return $this->morphTo();
	}

	public function getQueryStringAttribute() {
		return (string) QueryNode::deserialize(json_decode($this->query_structure));
	}


	public function user() {
		return $this->belongsTo(User::class);
	}
	/*
	 * Get the size of the data in bytes
	 */
	public function getSizeAttribute() {
		$wat = mb_strlen($this->attributes['data'], 'UTF-8');
		return $wat;
	}


   /*
	 *  Given a query path, get the out value 
	 */
	public function getOutput($path) {

		// Example of a $path:
		// query.reddit.searchSubredditNames:String*
		//  '*' can be at any level (ex query.reddit*.searchSubredditNames:String*)
		//  a '*' denotes that output at that level can be a list
		$attributes = explode('.', $path);	

		$data =  collect([json_decode($this->data)->data]);

		foreach($attributes as $currentLevel) {
			// Remove the first attribute, always guarenteed to be 'query'
			if(array_search($currentLevel, $attributes) === 0) {
				continue;
			} 
			
			$isAttribute = array_search($currentLevel, $attributes) === count($attributes) -1; // whether we're at the last level

			$isList = QueryHistory::getEndOfString($currentLevel) == '*';
			if ($isList) {
				$currentLevel = explode('*', $currentLevel)[0];
			}

			// The last attribute will have a ':', which we don't want and we have to handle as a special case
			if ($isAttribute) {
				$currentLevel = explode(':', $currentLevel)[0];
			}

					
			$data = $data->map(function($value) use ($currentLevel, $data){
				// If we have a null response, continue the chain of nulls
				if ($value === null) {
					return $value;	
				}
				return $value->$currentLevel;
			});
			if($isList) {
				$data = $data->flatten();
			}
		}

		return $data;
	}

	private static function getEndOfString($string) {
		return substr($string, strlen($string)-1, strlen($string));
	}
}
