@extends('layouts.app')

@section('content')
<div style="margin-right: 20px; margin-left: 20px">
		
		@if ($user->quotaUsed >= $user->quota)
		<div class="row justify-content-center">
				<div class="alert alert-danger" role="alert">
					You have used {{ $user->quotaUsed }} GB of storage out of your quota of {{ $user->quota }} GB. Remove some stored data in order to continue to query.
				</div>
		</div>
		@elseif (($user->quotaUsed / $user->quota) >= 0.5)
		<div class="row justify-content-center">
				<div class="alert alert-warning" role="alert">
					You have used {{ $user->quotaUsed }} GB of storage out of your quota of {{ $user->quota }} GB. To avoid unnecessary service interruption remove stored data.
				</div>
		</div>
		@endif

		<div class="row"> <!-- Begin first row -->
			<div class="col-md-6">
					<div class="card border-dark mb-3">
						<div class="card-header bg-dark text-light"> Authorize Social Media Providers
							<div class="float-right">
							<context-info icon-info="fas fa-question fa-lg" popup-info="In order to build queries for each social media provider, you need to log into an account and authorize this system to perform queries on your behalf. Providers you haven't authorized are displayed in grey and the providers you have authorized are colored."></context-info>
						</div>
						</div>
						<div class="card-body">
						@foreach($servers as $server)
							<a href="{{ route('authorizeProvider', ['provider' => $server->slug]) }}"> 
								<provider-authorization-status provider-slug="{{$server->slug}}" active="{{$server->active}}" icon-size="5"> </provider-authorization-status>
							</a>
						@endforeach

						</div>
					</div>
				</div>
			<div class="col-md-6">
				<div class="card border-dark mb-3">
					<div class="card-header bg-dark text-light">  Manage Applications
						<div class="float-right">
							<context-info icon-info="fas fa-question fa-lg" popup-info="Applications are servers that receive your data when you query. By default we assume that you want access to the data processing server but if you have a server of your own that you want to have access to your data, then you can just add it here and when you submit a query it will receive the data"></context-info>
						</div>
					</div>
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
	</div> <!-- End First Row -->

		<div class="row">  <!-- Begin second row -->
			<div class="col-md-6"> <!-- Begin first row item -->
				<div class="card border-dark mb-3">
					<div class="card-header bg-dark text-light">  Manage Queries
							<div class="float-right">
							<context-info icon-info="fas fa-question fa-lg" popup-info="Queries represent traversals through a graph of relationships. Here you can create queries depending on social media providers that you've authorized. After you create a query, you can manage it to see the raw data from the query, what attributes you've chosen, the history, as well as update it."></context-info>
						</div>
					</div>
					<div class="card-body">
						<a href="{{ url('queries/create') }}" class="btn btn-primary"> Create Query </a>
						<hr/>
						@if (count($user->queries) > 0)
						<ul class="list-group" style="height: 400px; overflow-y: scroll">

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
			</div>  <!-- End first row item -->

			<div class="col-md-6"> <!-- Begin second row item -->
				<div class="card border-dark mb-3">
					<div class="card-header bg-dark text-light">  Manage Meta-Queries 
							<div class="float-right">
							<context-info icon-info="fas fa-question fa-lg" popup-info="Meta Queries represent chaining of queries together. You take the output of one query and use it as the input of another query."></context-info>
						</div>
					</div>
					<div class="card-body">
						<a href="{{ url('meta-queries/create')}}" class="btn btn-primary"> Create Meta Query </a>
						<hr/>
						@if (count($user->metaQueries) > 0)
						<ul class="list-group" style="height: 400px; overflow-y: scroll">

							@foreach ($user->metaQueries->sortByDesc('created_at') as $query)
							<li class="list-group-item"> 
									{{ $query->name }} 
									<br>
										<a href="{{ url('meta-queries/'.$query->id) }}" class="btn btn-info">
											<i class="fas fa-history fa-lg"></i>
											Manage
										</a>
							</li>
							@endforeach

						</ul>
						@endif 

					</div>
				</div>
				</div> <!-- end second row item -->
			</div> <!-- End second row --> 

</div> <!-- end container -->
@endsection
