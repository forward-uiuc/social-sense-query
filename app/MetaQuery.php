<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetaQuery extends Model
{

	protected $fillable = ['schedule','canvas','topology'];
	protected $table = 'meta_queries';

	/*
	 * Get the user this MetaQuery belongs to
	 */
	public function user() {
		return $this->belongsTo('App\User');
	}


	/*
	 * Retrieve all the nodes for this 
	 * meta query
	 */
	public function queries() {
		return $this->hasMany('App\Query');
	}

	/*
	 * Submit this meta query
	 */
	public function submit() {
		dd("SUBMIT");
	}
}
