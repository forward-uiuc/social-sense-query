@extends('layouts.app')

@section('content')

<div class="container">
	<meta-query-builder :queries='{{ $queries->map(function($q){ return ["name" => $q->name, "id" => $q->id, "structure" => json_decode($q->structure)]; }) }} '> </meta-query-builder>
</div>
@endsection
