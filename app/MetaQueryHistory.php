<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetaQueryHistory extends Model
{
	protected $table = 'meta_query_histories';
	protected $fillable = ['duration','data', 'has_error', 'structure','string'];

	/*
	 * Get the query that generated this history item
	 */
	public function queryOfRecord() {
		return $this->belongsTo('App\MetaQuery');
	}
}
