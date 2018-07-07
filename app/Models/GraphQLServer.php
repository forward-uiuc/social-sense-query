<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Builders\GraphQLRequestBuilders\StarWarsGQLRequestBuilder;

class GraphQLServer extends Model
{
	protected $table = 'graphql_servers';
	protected $fillable = ['name','slug', 'url', 'description'];
	protected static $requestBuilders = [
		'starwars' => StarWarsGQLRequestBuilder::class
	];

	/*
	 * Get the queries that go to this server
	 */
	public function queries() {
		return $this->hasMany('App\Models\Query\Query', 'server_id');
	}

	/**
	 * Returns a guzzlehttp request
	 */
	public function buildRequest(String $queryString, User $user) {
		try {
			if (array_key_exists($this->slug, GraphQLServer::$requestBuilders)) {
				return GraphQLServer::$requestBuilders[$this->slug]::build($this, $user, $queryString);
			} else {
				return \App\Builders\GraphQLRequestBuilders\DefaultGQLRequestBuilder::build($this, $user, $queryString);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}
