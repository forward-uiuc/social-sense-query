@extends('layouts.app')

@section('content')

<div class="container">
		@if ($errors->has('string'))
			<h1>  {{ $errors->first('string')  </h1>
		@endif

		@if ($errors->has('structure'))
			<h1>  {{ $errors->first('structure')  </h1>
		@endif

    {!! Form::open(['url' => 'queries', 'id' => 'createQuery']) !!}
			@csrf		
			<query-builder authorized-providers={!! json_encode($user->authorizedProviders) !!} form-id='createQuery'> </query-builder>
		{!! Form::close() !!}
</div>
@endsection
