@extends('layouts.app')

@section('content')

	<div class="row" style="margin-top: 20px;">
		<div class="offset-md-2 col-md-8">
			<table class="table table-striped table-dark">
				<thead>
					<tr>
						<th scope="col"> Name </th>
						<th scope="col"> Quota Used </th>
						<th scope="col"> Quota Available </th>
					</tr>
				</thead>
				<tbody>
				@foreach ($users as $user)
					<tr>
						<td> {{ $user->name }}  </td>
						<td> {{ $user->quotaUsed }} GB </td>
						<td> {{ $user->quota }} GB </td>
						<td>
							<a href="{{ url('users/'.$user->id.'/edit') }}" class="btn btn-warning">
								<i class="fas fa-cogs fa-lg"></i>
								Edit User
							</a>
						</td>
						
						<td>
							<form id="delete-form" action="{{ route('users.destroy', ['id' => $user->id]) }}" method="POST" style="display: none;">
								@method('delete')
								@csrf
						 </form>

						<a href="" 
							 class="btn btn-danger float-right" 
							 onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
								<i class="fas fa-minus-circle"></i>
								Delete User 
						</a>
					</td>

					</tr>	
				@endforeach
				</tbody>
		</table>
		</div>
	</div>

@endsection
