@extends('layouts.app')

@section('content')
<div class="row" style='margin-left: 20px'>
	<h3> We're really happy that you're interested in our system! If you could provide the following bits of info, we will be in contact shortly. </h3>
	<form action="/contact" method="POST">
		@csrf
	  <div class="form-group">
		<label for="email">Email address</label>
		<input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" required>
		<small id="emailHelp" class="form-text text-muted">How can we best get in contact with you? We'll never share your email with anyone else.</small>
	  </div>

	  <div class="form-group">
		<label for="name">Name</label>
		<input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" placeholder="Enter your name" required>
		<small id="nameHelp" class="form-text text-muted">However you (or your organization) would like to be addressed.</small>
	  </div>

	  <div class="form-group">
		<label for="about">What specifically interests you in our system? This is optional, but it can help us iterate and improve in the future.</label>
		<textarea class="form-control" id="about" name="about" rows="3"></textarea>
	  </div>
	  <button type="submit" class="btn btn-primary">Submit</button>
	</form>
</div>

@endsection
