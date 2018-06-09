<?php

namespace App\Builders\GraphQLRequestBuilders;
use App\Models\GraphQLServer;
use GuzzleHttp\Client;

class DefaultGQLRequestBuilder implements BuildsGraphQLRequests
{

	public static function build(GraphQLServer $server, String $queryString) {

		$client = new Client([
			'base_uri' => $server->url
		]);


		return $client->request('POST', 'graphql', [
			'headers' => [
//					'Content-Type' => 'application/json'
			],
			'query' => ['query' => $queryString],
		])->getBody()->getContents();
	}
}
