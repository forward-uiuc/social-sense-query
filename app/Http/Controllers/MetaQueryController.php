<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetaQuery\MetaQuery;
use App\Models\MetaQuery\Run;
use App\Models\MetaQuery\Stage;
use App\Models\Query\QueryHistory;

class MetaQueryController extends Controller
{
		/**
     * Create a new controller instance.
     *
     * @return void
     */
 	public function __construct()
  {
  	$this->middleware('auth');
  }

	/**
	 * Return a view to create new meta queries
	 * 
	 */
	public function create() 
	{
		$user = \Auth::user();
		$queries = $user->queries;
		return view('meta-queries.create', ['queries' => $queries]);
	}


	/**
	 * Store a meta query
	 */
	public function store(Request $request)
	{
			$user = \Auth::user();
			$user->metaQueries()->save(new MetaQuery($request->all()));

			$request->session()->flash('status', 'MetaQuery Stored!');
			return redirect('home');
	}

 /*	
	* submit a meta query
	*/
	public function submit(Request $request, $id) 
	{
		$query = MetaQuery::findOrFail($id);
		try {
			$data = $query->submit();
			$run = new Run(['topology' => $query->topology]);
			$query->runs()->save($run);

			$data->each(function($stageResponses) use ($run){ 
				$stage = new Stage();
				$run->stages()->save($stage);

				$stageResponses->each(function($metaQueryNodeResponses) use ($stage){
					$metaQueryNodeResponses->each(function($queryResponse) use ($stage) {
						$queryResponse->data = json_encode($queryResponse->data);
						$history = new QueryHistory((array) $queryResponse);
						$history->user_id = \Auth::user()->id;
						$stage->history()->save($history);
					});
				});
			});


		} catch (UserQuotaReachedException $e) {
			$request->session()->flash('error', $e->getMessage());
		} catch (\RuntimeException $e) {
			$request->session()->flash('error', $e->getMessage());
		}

		return $this->show($request, $id);
	}

	/**
	 * Show a meta query
	 */
	public function show(Request $request, $id) {
		$query = MetaQuery::where('id', $id)->with('runs.stages.history')->first();
		$query->topology = json_decode($query->topology);
		$query->runs->each(function($run){
			$run->topology = json_decode($run->topology);
			$run->stages->each(function ($stage) {
				$stage->history->each(function($historyItem) {
					$historyItem->string = $historyItem->queryString;
					$historyItem->dataObject = json_decode($historyItem->data);
					$historyItem->query = json_decode($historyItem->query_structure);
				});
			});
		});

		return view('meta-queries.show', ['query' => $query]);
	}
}
