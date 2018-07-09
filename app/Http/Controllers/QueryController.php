<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQueryRequest;
use App\Models\Query\Query;
use App\Models\Query\QueryHistory;
use App\Models\GraphQLServer;

use App\Exceptions\UserQuotaReachedException;

class QueryController extends Controller
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
     * Display a listing of the query.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
			return abort(404);
    }

    /**
     * Show the form for creating a new query.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
			$user = \Auth::user();
			$servers = GraphQLServer::where('schema', '!=', 'null')->get();

			// First, Catch a situation of there being no servers added
			if ($servers->count() < 1) {
				$request->session()->flash('error', 'Error! There are no active servers with schema available to query.');
				return back();
			}

			// Next, filter on the servers that this user can query.
			// If they require authorization, the user must provide one. Otherwise they cannot use that server
			// Likewise if the server doesn't require authorizaiton they can query it
			$servers = $servers->filter(function($server) use ($user) {
				if($server->requires_authentication || $server->requires_authorization) {
					$userAuthorization = $user->authorizations()->where('server_id', $server->id)->first();

					if($userAuthorization){
						return true; 
					} else {
						return false;
					}
	
				}
				
				return true;	
			});

			// At this point, if there are no servers available they need to authorize.
			if ($servers->count() < 1) {
				$request->session()->flash('error', 'Error! You need to provide authorization in order to query the servers available.');
				return back();
			}

			return view('queries.create', ['user' => $user, 'servers' => $servers]);
    }

	/**
	 * Submit a query
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param $id
	 */
	public function submit(Request $request, $id) 
	{
		$query = Query::findOrFail($id);

		try {

			$query->submit();

		} catch (UserQuotaReachedException $e) {
			$request->session()->flash('error', $e->getMessage());
		} catch (\RuntimeException $e) {
			$request->session()->flash('error', $e->getMessage());
		} 
		return $this->show($id);
	}


    /**
     * Store a newly created query in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQueryRequest $request)
    {
			$user = \Auth::user();
			$q = new Query($request->all());
			$user->queries()->save($q);
			$request->session()->flash('status', 'Query Stored!');
			return redirect('home');
    }

    /**
     * Display the specified query.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
			$query = Query::with('history')->where('id',$id)->firstOrFail();
			return view('queries.show', ['query' => $query]);
    }

    /**
     * Show the form for editing the specified query.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
			$user = \Auth::user();
			$query = Query::findOrFail($id);
			$servers = collect([$query->server]);
			return view('queries.update', ['user' => $user, 'query' => $query, 'servers' => $servers]);
    }

    /**
     * Update the specified query in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
			$query = Query::findOrFail($id);
			$query->fill($request->all())->save();
			$request->session()->flash('status', 'Query Updated!');
			return $this->show($id);
    }

    /**
     * Remove the specified query from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
			Query::findOrFail($id)->delete();
			$request->session()->flash('status', 'Query Deleted!');
			return redirect('home');
    }
}
