@extends('layouts.app')

@section('content')

<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8">
					<div class="card border-dark mb-3">
						<div class="card-header bg-dark text-light"> Authorize Social Media Providers</div>
						<div class="card-body">
							@foreach ($user->authorizedProviders as $providerName => $authorized)
								<a href="{{ url('login/' . $providerName) }}">
								@include ('providers.' . $providerName, ['size' => 'col-lg-3', 'active' => $authorized])
								</a>
							@endforeach
						</div>
					</div>
				</div>
		</div>

		<div class="row" style="margin-top:20px;">
			<div class="col-md-6">
				<div class="card border-dark mb-3">
					<div class="card-header bg-dark text-light">  Manage Queries</div>
					<div class="card-body">
						<a href="{{ url('queries/create') }}" class="btn btn-primary"> Create Query </a>
						<hr/>
						@if (count($user->queries) > 0)
						<ul class="list-group">

							@foreach ($user->queries as $query)
							<li class="list-group-item"> 
									{{ $query->name }} 

									<br>

										<a href="{{ url('queries/'.$query->id) }}" class="btn btn-info">
											<i class="fas fa-history fa-lg"></i>
											Manage
										</a>
							</li>

							@endforeach

						</ul>
						@endif 

					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="card border-dark mb-3">
					<div class="card-header bg-dark text-light">  Manage Applications </div>
					<div class="card-body">
						<ul class="list-group">
							@foreach ($user->applications as $application) 
								<a href="{{url('') . $application->home}}" target="_blank">
									<li class="list-group-item"> {{ $application->name }} </li>
								</a>
							@endforeach
						</ul>
				</div>
			</div>
		</div>

</div>
@endsection
