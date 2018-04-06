<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Query;

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
}
