<?php

namespace App\Builders\GraphQLRequestBuilders;
use App\Models\GraphQLServer;
use App\Models\User;

use GuzzleHttp\Client;

class DefaultGQLRequestBuilder implements BuildsGraphQLRequests
{

	public static function build(GraphQLServer $server, User $user, String $queryString) {

		$client = new Client([
			'base_uri' => $server->url
		]);

		$headers = [
			'Content-type' => 'application/graphql'
		];

		if ( $server->requires_authorization) {

			$authorization = $user->authorizations()->where('server_id', $server->id)->firstOrFail();
			$headers['Authorization'] = json_encode([
					'accessToken' => $authorization->access_token,
					'refreshToken' => $authorization->refresh_token,
					'clientId' => config('services.'.$server->slug.'.client_id'),
					'clientSecret' => config('services.'.$server->slug.'.client_secret'),
					'meta' => $authorization->meta
			]);
		}

		return $client->request('POST', '', [
			'headers' => $headers,
			'body' => $queryString
		])->getBody()->getContents();
	}
}
