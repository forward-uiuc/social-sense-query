<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
 * A query mapping represents an edge
 * for MetaQuery graphs. The 'from' specifies
 * what node is required to resolve before sending
 * the output to the 'to' node
 */
class QueryMapping extends Model
{
	public function from() {
		return $this->belongsTo('App\Query', 'id', 'from_query_id');
	}

	public function to() {
		return $this->belongsTo('App\Query', 'id', 'to_query_id');
	}

	public function metaQuery() {
		return $this->belongsTo('App\MetaQuery');
	}
}
