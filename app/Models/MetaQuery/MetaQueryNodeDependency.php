<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

/* 
* A MetaQueryNodeDency is
*  a persisted mapping between the input of one node to the output of another node
*/
class MetaQueryNodeDependency extends Model
{
	protected $fillable = ['meta_query_node_input_id', 'meta_query_node_output_id'];

	/*
	 * Which Input does this dependency go to
	**/
	public function input() {
		return $this->belongsTo(MetaQueryNodeInput::class, 'meta_query_node_input_id');
	}

	/*
	 * Which output does the dependency rely on
	*/
	public function output() {
		return $this->belongsTo(MetaQueryNodeOutput::class, 'meta_query_node_output_id');
	}
	
	/*
	* Which node has this dependency (same result as $this->input->node) 
	**/
	public function dependantNode() {
		return $this->belongsTo(MetaQueryNode::class, 'meta_query_node_id');
	}
}
