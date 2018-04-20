<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class MetaQueryFunction extends Model
{
	protected $fillable = ['name'];

	/*
	 * Get what MetaQueryNodes this function is part of
	 */
	public function nodes() {
		return $this->morphMany(MetaQueryNode::class, 'node');
	}
}
