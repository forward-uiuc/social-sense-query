@extends('layouts.app')
@section('content')

<div style="margin-left: 10px">
	<div class="row" style="margin-top: 20px;">
		<div class="offset-md-2 col-md-8">
			<table class="table table-striped table-dark">
				<thead>
					<tr>
						<th scope="col"> Server Name</th>
						<th scope="col"> URL </th>
						<th scope="col"> Slug (Used programatically) </th>
						<th scope="col"> Description</th>
						<th scope="col"> Requires Authentication </th>
						<th scope="col"> Requires Authorization </th>
						<th scope="col"> Refresh Schema  </th>
						<th scope="col"> Update </th>
					</tr>
				</thead>
				<tbody>
				@foreach ($servers as $server)
					<tr>
						<td> {{$server->name  }} </td>	
						<td> {{$server->url}} </td>	
						<td> {{$server->slug}} </td>	
						<td> {{$server->description}} </td>	
						<td> {{$server->requires_authentication}} </td>	
						<td> {{$server->requires_authorization}} </td>	
						<td> <a href="{{ route('servers.refresh', ['server' => $server->id]) }}" class="btn btn-info" > Refresh </a> </td> 
						<td> <a href="{{ route('servers.edit', ['server' => $server]) }}" class="btn btn-warning"> Edit Server </a> </td>
					</tr>	
				@endforeach
				</tbody>
		</table>
		<a href="{{ route('servers.create') }}" class="btn btn-primary"> Add a server </a>


		</div>
	</div>

</div>



@endsection
