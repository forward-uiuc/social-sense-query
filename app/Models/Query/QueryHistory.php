<?php

namespace App\Models\Query;

use Illuminate\Database\Eloquent\Model;

use Query\QueryNode;

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
		return $this->belongsTo('App\User');
	}
	/*
	 * Get the size of the data in bytes
	 */
	public function getSizeAttribute() {
		return mb_strlen($this->attributes['data'], 'UTF-8');
	}
}
