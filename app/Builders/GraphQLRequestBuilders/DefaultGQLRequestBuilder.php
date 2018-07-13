<?php

namespace App\Builders\GraphQLRequestBuilders;
use App\Models\GraphQLServer;
use App\Models\User;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\ServerDownException;

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

		try {
			return $client->request('POST', '', [
				'headers' => $headers,
				'body' => $queryString
			])->getBody()->getContents();
		} catch (ClientException $e) { // If the request results in a client error, return that
			return $e->getResponse()->getBody()->getContents();
		} catch (\Exception $e) { // If it's any other kind of error then it's not the user's fault and, thus, let them know something sad happend
			report($e);
			throw new ServerDownException($e->getMessage());
		}

		return null;
	}
}
