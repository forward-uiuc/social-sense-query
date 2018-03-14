<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
	protected $fillable = [	'callback_url', 'home', 'name', 'description'];

	/*
	 * Get the user who owns this callback
	 */
	public function user() {
		return $this->belongsTo('App\User');
	}
}
