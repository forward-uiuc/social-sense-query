<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{

	public function nodes() {
		return $this->hasMany(MetaQueryNode::class);
	}

	/*
	 * Get which run of a MetaQuery that this stage belongs to
	 */
	public function run() {
		return $this->belongsTo(Run::class);
	}

}
