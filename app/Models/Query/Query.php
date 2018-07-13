<?php

namespace App\Models\Query;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

use App\Exceptions\UserQuotaReachedException;
use App\Models\MetaQuery\MetaQueryNode;

/*
 * A query represents a GraphQL query belonging to a user.
 * A query history represents the output of having ran this query.
 * This query can be part of a MetaQuery, either just as an output
 * or as part of a graph as input to another query
 */
class Query extends Model
{

	protected $fillable = ['name','schedule','structure','description','string', 'server_id'];

	/*
	 * Get the user this query belongs to
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
	}

	public function server() {
		return $this->belongsTo('App\Models\GraphQLServer', 'server_id');
	}

	/*
	 * Get the history of this query
	 */
	public function history() {
		return $this->hasMany(QueryHistory::class);
	}

	public function metaQueryNodes() {
		return $this->morphMany(MetaQueryNode::class, 'node');
	}

	
	public function getQueryNode() {
		$node = json_decode($this->structure);
		return QueryNode::deserialize(json_decode($this->structure));
	}

	public function getQueryString() {
		return (string) $this->getQueryNode();
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
	 * Submit this query to the graphql server, returning attributes of a history object
	 */
	public function submit() {

		// First, check to see if we've used too much of the user's quota
		
		$quotaUsed = $this->user->quotaUsed;
		$quotaAvailable = $this->user->quota;
		if ($quotaUsed >= $quotaAvailable) {
			throw new UserQuotaReachedException("Error, used " . $quotaUsed . " GB of " . $quotaAvailable . " GB quota.");

		}
		
		$start = microtime(true);
		$body = $this->server->buildRequest($this->getQueryString(), $this->user);

		$duration = round(microtime(true) - $start, 3) * 1000;	
		$hasError = property_exists(json_decode($body), 'errors');

		$result  = ['data' => $body, 'has_error' => $hasError, 'duration' => $duration];
		
		$history = new QueryHistory($result);
		$history->query_structure = $this->structure;
		$history->user_id = $this->user->id;

		$this->history()->save($history);
	}
}
