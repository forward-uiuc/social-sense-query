<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GraphQLServer;

class GraphQLServerController extends Controller
{

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the query.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
			$servers = GraphQLServer::all();
			return view('servers.list', ['servers' => $servers] );
    }

    /**
     * Show the form for creating a new query.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
			return view('servers.create');
    }


    /**
     * Store a newly created GraphQLServer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$server = new GraphQLServer($request->all());
		$server->requires_authentication = (bool) $request->input('requires_authentication');
		$server->requires_authorization = (bool) $request->input('requires_authorization');
		$server->save();
		\Log::info('Server saved:' . $server->name);
		$request->session()->flash('status', 'Server Saved! Remember, if this server requires authentication or authorization to add the appropriate builder for requests, support for authentication, and icon support if applicable');

		return $this->index();
    }

    /**
     * Display the specified query.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
			$server = GraphQLServer::where('id', $id)->firstOrFail();
			return view('servers.show')->compact(['server' =>  $server]);
    }

    /**
     * Show the form for editing the specified GraphQLServer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		$server = GraphQLServer::findOrFail($id);
		return view('servers.update', ['server' =>  $server]);
    }

    /**
     * Update the specified GraphQLServer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GraphQLServer $server)
    {
		$server->fill($request->all())->save();
		$request->session()->flash('status', 'Server Updated!');
		return $this->index();
    }

	public function refresh(Request $request, GraphQLServer $server) 
	{
		$introspectionQuery = \File::get( resource_path() . '/utils/introspectionQuery.txt');

		try {
			$server->schema = $server->buildRequest($introspectionQuery, \Auth::user());
			$server->save();
			$request->session()->flash('status', 'Schema Updated');	
		} catch (\Exception $e) {
			$request->session()->flash('error', 'Ughoh, something went wrong: ' . $e->getMessage());
		} 

		return $this->index();
	}

    /**
     * Remove the specified server from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
		abort(500, 'That will cause a grave consistency error friend');
    }
	
}
