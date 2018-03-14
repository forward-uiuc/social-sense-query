@extends('layouts.app')
@section('content')
	<div class="flex-center position-ref full-height">
		<div class="content">
				<div class="title m-b-md">
						Social Sense
				</div>
				<div class="links">
						<a href="{{ url('/about') }}">About</a>
						<a href="{{ url('/contact') }}">Contact Us</a>
						<a href="https://github.com/laravel/laravel">GitHub</a>
				</div>
		</div>
	</div>
@endsection
