<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
	/*
	 * Get the history items of this stage
	 */
	public function history() {
		return $this->morphMany('App\Models\Query\QueryHistory','query');
	}

	/*
	 * Get which run of a MetaQuery that this stage belongs to
	 */
	public function run() {
		return $this->belongsTo(Run::class);
	}

}
