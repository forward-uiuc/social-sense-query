<?php

namespace App\Models\Query;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

use App\Exceptions\UserQuotaReachedException;

/*
 * A query represents a GraphQL query belonging to a user.
 * A query history represents the output of having ran this query.
 * This query can be part of a MetaQuery, either just as an output
 * or as part of a graph as input to another query
 */
class Query extends Model
{

	protected $fillable = ['name','schedule','structure','description','string'];

	/*
	 * Get the user this query belongs to
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
	}

	/*
	 * Get the history of this query
	 */
	public function history() {
		return $this->morphMany(QueryHistory::class,'query');
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
	 * Retrieve all the query mappings that
	 * has this query as a source for inputs
	 */
	public function sourceMappings() {
		return $this->hasMany('App\QueryMapping','from_query_id');
	}

	/*
	 * Retrieve all teh query mappings that
	 * has this query as a destination for inputs
	 */
	public function destinationMappings() {
		return $this->hasMany('App\QueryMapping', 'to_query_id');
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
	 * Submit this query to the graphql server, returning attributes of a history object
	 */
	public function submit() {

		// First, check to see if we've used too much of the user's quota
		
		$quotaUsed = $this->user->quotaUsed;
		$quotaAvailable = $this->user->quota;
		if($quotaUsed >= $quotaAvailable){
			throw new UserQuotaReachedException("Error, used " . $quotaUsed . " GB of " . $quotaAvailable . " GB quota.");

		}

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

		} catch (ConnectException $e) {
			$duration = round(microtime(true) - $start, 3) * 1000;	
			return ['data' => json_encode($e->getMessage()), 'has_error' => true, 'duration' => $duration];

		}	catch (ServerException $e) {
			$duration = round(microtime(true) - $start, 3) * 1000;  
			return ['data' => json_encode($e->getMessage()), 'has_error' => true, 'duration' => $duration];
		}
	}
}
