@extends('layouts.app')

@section('content')
	<div class="row">
		<div class="offset-md-2 col-md-8">
			<h1> {{ $query->name }} </h1>
  		<div class="form-group">
				<label for="description"> Description: </label>
				<textarea id="description" class="form-control" rows="3" cols="10" readonly>{!! (e($query->description)) !!}</textarea> 
			</div>
				
			
			<a href="{{ url('queries/'.$query->id.'/submit') }}" class="btn btn-success">
				<i class="fas fa-play"></i>
				Run Query	
			</a>

			<a href="{{ url('queries/'.$query->id.'/edit') }}" class="btn btn-warning">
				<i class="fas fa-cogs fa-lg"></i>
					Edit Query
			</a>


				<form id="delete-form" action="{{ route('queries.destroy', ['id' => $query->id]) }}" method="POST" style="display: none;">
					@method('delete')
					@csrf
			 </form>

			<a href="" 
				 class="btn btn-danger float-right" 
				 onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
					<i class="fas fa-minus-circle"></i>
					Delete Query
			</a>
				
			@if ($query->schedule != null)
			<h3> Next Run Time: </h3>
				{!! $query->nextRunDate->format('H:i Y-m-d')  !!}
			@endif

		</div>	
	</div>


	<div class="row" style="margin-top: 20px;">
		<div class="offset-md-2 col-md-8">
			<table class="table table-striped table-dark">
				<thead>
					<tr>
						<th scope="col"> Time </th>
						<th scope="col"> Runtime (ms) </th>
						<th scope="col"> Data </th>
					</tr>
				</thead>
				<tbody>
				@foreach ($query->history->sortByDesc('created_at') as $history)
					@if (@$history->has_error)
					<tr class="bg-danger"> 
					@else
					<tr>
					@endif
						<td> {{ $history->created_at }}  </td>
						<td> {{ $history->duration }} </td>
  					<td> <tree-view :data="{{ $history->data }}" :options="{maxDepth: 1}"></tree-view> </td>
					</tr> 

				@endforeach
				</tbody>
		</table>
		</div>
	</div>

@endsection
