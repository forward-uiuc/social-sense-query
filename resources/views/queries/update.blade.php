@extends('layouts.app')

@section('content')

	@if ($errors->has('structure'))
		<h1>  {{ $errors->first('structure') }}  </h1>
	@endif

    {!! Form::open(['url' => 'queries/'.$query->id, 'id' => 'updateQuery']) !!}
		@method('put') 
		<query-editor :servers="{{$servers->toJson()}}" form-id="updateQuery" initial-name="{{$query->name}}" initial-schedule="{{$query->schedule}}" initial-structure="{{$query->structure}}"></query-editor>
	{!! Form::close() !!}
@endsection
