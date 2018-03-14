<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use App\QueryHistory;

class Query extends Model
{
	protected $fillable = ['name','schedule','structure','description','string'];

	/*
	 * Get the user this query belongs to
	 */
	public function user() {
		return $this->belongsTo('App\User');
	}

	/*
	 * Get the history of this query
	 */
	public function history() {
		return $this->hasMany('App\QueryHistory');
	}

	/*
	 * Get whether this query is due for execution
	 */
	public function isDue($date = 'now') {
		if($this->schedule == null){
			return false;
		}
		return CronExpression::factory($this->schedule)->isDue($date);
	}

	/*
	 * Get the next date that this query will run in UTC
	 */
	public function getNextRunDateAttribute() {
		if($this->schedule == null){
			return -1;
		}

		return CronExpression::factory($this->schedule)->getNextRunDate();
	}


	/*
	 * Submit this query to the graphql server, creating a new history
	 */
	public function submit() {
		$client = new Client([
			'base_uri' => config('services.graphql.server_uri')
		]);


		$start = microtime(true);
		try{

			$response = $client->request('POST', 'graphql', [
				'headers' => [
						'Content-Type' => 'application/json'
				],
				'query' => ['query' => $this->string],
				'body' => $this->user->authorizations->toJSON()
			]);
			$duration = round(microtime(true) - $start, 3) * 1000;	
			$body = (string) $response->getBody();
			$res = json_decode($body);
			$history = ['data' => $body, 'has_error' => false, 'duration' => $duration];
			return $history;

		} catch (ClientException $e){
			$duration = round(microtime(true) - $start, 3) * 1000;	
			$data = (string) $e->getResponse()->getBody(true);
			return ['data' => $data, 'has_error' => true, 'duration' => $duration];
		} 	

	}
}
