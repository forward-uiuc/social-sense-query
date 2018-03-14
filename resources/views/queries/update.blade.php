@extends('layouts.app')

@section('content')

<div class="container">
		@if ($errors->has('string'))
			<h1>  {{ $errors->first('string') }}   </h1>
		@endif

		@if ($errors->has('structure'))
			<h1>  {{ $errors->first('structure') }}  </h1>
		@endif

    {!! Form::open(['url' => 'queries/'.$query->id, 'id' => 'updateQuery']) !!}
			@method('put') 
			<query-builder :authorized-providers={!! json_encode($user->authorizedProviders) !!}  
										 :serialized-query='echo({!! $query->structure !!})' form-id='updateQuery'
										 query-name="{!! $query->name !!}"
										 query-schedule="{!! $query->schedule !!}"> 
			
			</query-builder>
		{!! Form::close() !!}
</div>
@endsection
