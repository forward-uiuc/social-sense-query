<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SubmitMetaQueryRun;
use App\Models\MetaQuery\MetaQuery;
use App\Models\MetaQuery\Run;
use App\Models\MetaQuery\Stage;
use App\Models\Query\QueryHistory;
use App\Factories\RunFactory; 
use App\Models\MetaQuery\MetaQueryFunction;

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
		$functions = MetaQueryFunction::all();
		return view('meta-queries.create', ['queries' => $queries, 'functions' => $functions]);
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
	public function submit(Request $request, $id, RunFactory $runFactory) 
	{
		$query = MetaQuery::findOrFail($id);

		try{ 

			$run = $runFactory->createRun($query);
			$query->runs()->save($run);
			SubmitMetaQueryRun::dispatch($run);
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
		$withProperties = [
			'runs.stages.nodes.inputs',
			'runs.stages.nodes.outputs',
			'runs.stages.nodes.node',
			'runs.stages.nodes.dependencies',
			'runs.stages.nodes.dependencies.input.node',
			'runs.stages.nodes.dependencies.output.node',
		];

		$query = MetaQuery::where('id', $id)->with($withProperties)->first();
		return view('meta-queries.show', ['query' => $query]);
	}
}
