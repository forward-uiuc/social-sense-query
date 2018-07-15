<?php

namespace App\Models\MetaQuery;

use Illuminate\Database\Eloquent\Model;

use App\Models\Query\QueryHistory;
use App\Models\User;

class MetaQuery extends Model
{

	protected $fillable = ['schedule','canvas','topology','name'];
	protected $table = 'meta_queries';

	/*
	 * Get the user this MetaQuery belongs to
	 */
	public function user() {
		return $this->belongsTo(User::class);
	}


	/*	
	* Get the runsof this query. T
	*/
	public function runs() {
		return $this->hasMany(Run::class);
	}

}
