<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InterestedParty;

class InterestedPartyController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
			$interestedParties = InterestedParty::all();
			return view('contact.list', ['interestedParties' => $interestedParties]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		return view('contact.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$userInfo = new InterestedParty($request->all());
		$userInfo->save();
		$request->session()->flash('status', "Thanks for the info! We'll be in contact as soon as possible!");
		return redirect('/');
    }

 }
