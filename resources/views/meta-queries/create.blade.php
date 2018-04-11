@extends('layouts.app')

@section('content')

<div class="offset-md-1">
    {!! Form::open(['url' => 'meta-queries', 'id' => 'createMetaQuery']) !!}
			<meta-query-builder form-id='createMetaQuery' :queries='{{ $queries->map(function($q){ return ["name" => $q->name, "id" => $q->id, "structure" => json_decode($q->structure)]; }) }} '> </meta-query-builder>
		{!! Form::close() !!}
</div>
@endsection
