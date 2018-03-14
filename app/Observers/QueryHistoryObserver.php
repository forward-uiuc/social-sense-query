<?php 

namespace App\Observers;

use App\QueryHistory;
use GuzzleHttp\Client;

class QueryHistoryObserver 
{
    /**
     * Listen to the QueryHistory created event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(QueryHistory $history)
    {
			$payload = [
				'username' => $history->queryOfRecord->user->name,
				'data' => $history->data,
				'time' => $history->created_at->timestamp

			];

			$history->queryOfRecord->user->applications->each(function($application) use ($payload) {
				$client = new Client([
					'base_uri' =>  $application->callback_url
				]);

				try{
					$client->request('POST', [
						'headers' => 'application/json',
						'body' => $payload
					]);


				} catch (Exception $e){
				}

			});
    }
}
