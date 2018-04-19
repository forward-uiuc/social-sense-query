<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
	protected $fillable = ['topology'];

	/*
	 * Get which metaQuery this run belongs to
	 */
	public function metaQuery() {
		return $this->belongsTo('App\MetaQuery');
	}

	/*
	 * Get all the stages that this run had
	 */
	public function stages() {
		return $this->hasMany('App\Stage');
	}
}

