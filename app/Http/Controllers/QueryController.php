<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQueryRequest;
use App\Query;
use App\QueryHistory;
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
    public function create()
    {
			$user = \Auth::user();
			return view('queries.create', ['user' => $user]);
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
				$query->history()->save(new QueryHistory($query->submit()));
			} catch (UserQuotaReachedException $e) {
				$request->session()->flash('error', $e->getMessage());
			}
			return back();
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
			$user->queries()->save(new Query($request->all()));

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
			return view('queries.update', ['user' => $user, 'query' => $query]);
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
			return view('queries.show', ['query' => $query]);
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
