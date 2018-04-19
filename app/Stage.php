<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
	/*
	 * Get the history items of this stage
	 */
	public function history() {
		return $this->morphMany('App\QueryHistory','query');
	}

	/*
	 * Get which run of a MetaQuery that this stage belongs to
	 */
	public function run() {
		return $this->belongsTo('App\Run');
	}

}
