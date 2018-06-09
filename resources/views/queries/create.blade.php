@extends('layouts.app')

@section('content')

	{!! Form::open(['url' => 'queries', 'id' => 'createQuery']) !!}
		<query-editor :servers="{{$servers->toJson()}}" form-id="createQuery"></query-editor>
	{!! Form::close() !!}
@endsection
