<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'quota'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

	/*
	 * Get all callbacks for the user
	 */
	public function applications() {
		return $this->hasMany(Application::class);
	}

	/*
	 * Get this user's authorizations
	 */
	public function authorizations() {
		return $this->hasMany(Authorization::class);
	}

	public function getAuthorizedProvidersAttribute() {
		$authorized = [];
		$providers = config('services.providers');

		foreach($providers as $provider){
			$authorization = $this->authorizations()->where('provider', $provider)->first();
			$authorized[$provider] = ($authorization == True);
		}
		return $authorized;
	}

	/*
	 * Get all the user's queries
	 */
	public function queries() {
		return $this->hasMany('App\Models\Query\Query');
	}

	/*
	 * Get all of this user's meta queries
	 */
	public function metaQueries() {
		return $this->hasMany('App\Models\MetaQuery\MetaQuery');
	}

	/*
	 * Get all of this user's history
	 */
	public function history() {
		return $this->hasMany('App\Models\Query\QueryHistory');
	}

	/*
	 * Get how many gigabytes of storage this user's data is taking up
	 */
	private $cached_quotaUsed = false;
	public function getQuotaUsedAttribute() {
		if($this->cached_quotaUsed){
			return $this->cached_quotaUsed;
		}

		$used = $this->history->reduce(function($carry, $queryHistory) {
			return $carry + $queryHistory->size;
		},0);

		$used = $used/(1024 * 1024 * 1024);
		$used = (int) ($used * 1000);
		$used = ((float) $used )/ 1000;
		$this->cached_quotaUsed = $used;
		return $used;
	}
}
