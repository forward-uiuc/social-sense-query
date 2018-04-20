<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class MetaQueryNodeInput extends Model
{
	public $timestamps = false;
	protected $fillable = ['path'];

	public function node() {
		return $this->belongsTo(MetaQueryNode::class, 'meta_query_node_id');
	}
}
