@extends('layouts.app')
@section('content')

<div class="offset-md-2 col-md-6">
	{!! Form::model($server, ['route' => ['servers.update', $server->id]]) !!}
		@method('PUT')
	
		<div class="form-group">
			{!! Form::label('name', 'Server Name') !!}
			{!! Form::text('name', null, ['class' => 'form-control']) !!}
		</div> 

		<div class="form-group">
			{!! Form::label('url', 'GraphQL Server URL') !!}
			{!! Form::text('url', null, ['class' => 'form-control']) !!}
		</div> 

		<div class="form-group">
			{!! Form::label('slug', 'Server slug (used programatically)') !!}
			{!! Form::text('slug', null, ['class' => 'form-control']) !!}
		</div> 

		<div class="form-group">
			{!! Form::label('description', 'Description') !!}
			{!! Form::textarea('description', null, ['class' => 'form-control']) !!}
		</div> 

		<div class="form-check">
			{!! Form::checkbox('requires_authentication', null, $server->requires_authentication, ['class' => 'form-check-input']) !!}
			{!! Form::label('requires_authentication', 'Requires Authentication', ['class' => 'form-check-label'] ) !!}
		</div> 


		<div class="form-check">
			{!! Form::checkbox('requires_authorization', null, $server->requires_authorization, ['class' => 'form-check-input']) !!}
			{!! Form::label('requires_authorization', 'Requires Authorization', ['class' => 'form-check-label'] ) !!}
		</div> 


		{!! Form::submit('Update', ['class' => 'btn btn-success', 'style' => 'margin-top:25px']) !!}
	{!! Form::close() !!} 

		<a href="{{route('servers.index')}}" class="btn btn-info float-right"> Back </a>
</div>


@endsection
