<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

use \App\Models\Query\Query;
use \App\Models\Query\QueryNode;

class GQLServerService
{
	/*
	 *  @var $client GuzzleHttp\Client;
	 */
	protected $client;

	public function __construct($uri)
	{
		$this->client  = new Client([
			'base_uri' => $uri
		]);
	}

	/*
	 * submitQuery
	 * @param $queryString a GraphQL Query String to submit
	 * @param $authorizations An Illuminate\Support\Collection of App\Authorization to authorize this request
	 */
	public function submitQueryString(string $queryString, Collection $authorizations)
	{

		$start = microtime(true);

		try{
			$response = $this->client->request('POST', 'graphql', [
				'headers' => [
						'Content-Type' => 'application/json'
				],
				'query' => ['query' => $queryString],
				'body' => $authorizations->toJSON()
			]);
			$duration = round(microtime(true) - $start, 3) * 1000;	
			$body = (string) $response->getBody();
			$res = json_decode($body);
			$result  = ['data' => $body, 'has_error' => false, 'duration' => $duration];
			return $result;

		} catch (Exception $e) {
			$duration = round(microtime(true) - $start, 3) * 1000;	
			return ['data' => json_encode($e->getMessage()), 'has_error' => true, 'duration' => $duration];
		}

	}
}
