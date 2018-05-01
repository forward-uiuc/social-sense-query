@extends('layouts.app')
@section('content')

		{!! Form::open(['url' => 'queries/'.$query->id, 'id' => 'deleteQuery', 'method' => 'delete']) !!}
			@csrf		
		{!! Form::close() !!}

		<query-viewer :query="{{ json_encode($query) }}" deleteFormId="deleteQuery"></query-viewer>
@endsection
