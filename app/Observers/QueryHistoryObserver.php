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
			if($history->has_error) {
				return;
			}

			$payload = [
				'username' => $history->queryOfRecord->user->name,
				'data' => $history->data,
				'time' => $history->created_at->timestamp

			];

			$history->queryOfRecord->user->applications->each(function($application) use ($payload, $history) {
				
				$client = new Client();

				try{
					$client->request('POST', $application->callback_url, [
						'headers' => [
							'Content-Type' => 'application/json',
						],
						'json' => $payload
					]);


				} catch (Exception $e){
				}

			});
    }
}
