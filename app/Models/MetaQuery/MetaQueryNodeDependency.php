<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

class MetaQueryNodeDependency extends Model
{
	public function input() {
		return $this->belongsTo(MetaQueryNodeInput::class, 'meta_query_node_input_id');
	}

	public function output() {
		return $this->belongsTo(MetaQueryNodeOutput::class, 'meta_query_node_output_id');
	}

	public function dependantNode() {
		return $this->belongsTo(MetaQueryNode::class);
	}
}
