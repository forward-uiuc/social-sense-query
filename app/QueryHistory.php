<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueryHistory extends Model
{
	protected $table = 'query_histories';
	protected $fillable = ['duration','data', 'has_error'];
	protected $appends = ['size']; 

	/*
	 * Get the query that generated this history item
	 */
	public function queryOfRecord() { // This cannot be named 'query' because that will override Illuminate\Database\Eloquent\Model->query()
		return $this->belongsTo('App\Query', 'query_id');
	}

	/*
	 * Get the size of the data in bytes
	 */
	public function getSizeAttribute() {
		return mb_strlen($this->attributes['data'], 'UTF-8');
	}
}
