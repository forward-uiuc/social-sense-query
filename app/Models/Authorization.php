<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
	protected $fillable = [
		'access_token', 'refresh_token', 'meta', 'provider'
	];

	protected $appends = ['client']; 

	/*
	 * Get the user of this authorization
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
	}

	public function getMetaAttribute() {
		return json_decode($this->attributes['meta']);
	}

	public function getClientAttribute() {
		return config('services.' . $this->attributes['provider']);
	}
}
