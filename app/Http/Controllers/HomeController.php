<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GraphQLServer;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$user = \Auth::user();
		$user->load(['queries','applications', 'authorizations']);
		$authorizations = $user->authorizations;
	
		$servers = GraphQLServer::all();
		$servers->each(function($server) use ($authorizations) {
			$server->active = $authorizations->contains(function($authorization, $key) use ($server){
				return $authorization->server_id === $server->id;
			});
		});
        return view('home', ['user' => $user, 'servers' => $servers]);
    }
}
