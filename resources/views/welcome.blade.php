@extends('layouts.app')
@section('content')
	<div class="flex-center position-ref full-height">
		<div class="content">
				<landing-page-headline primary="Listen Online" :options="['Reddit', 'Youtube', 'Twitter']"> </landing-page-headline>
				<div class="links">
						<a href="{{ url('/about') }}">About</a>
						<a href="{{ url('/contact/me') }}">Contact Us</a> 
						<a href="https://github.com/MagnificRogue/social-sense-meta">GitHub</a>
				</div>
		</div>
	</div>
@endsection
