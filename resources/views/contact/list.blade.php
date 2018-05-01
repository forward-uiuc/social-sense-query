@extends('layouts.app')

@section('content')
<div style="margin-left: 10px">
	<div class="row" style="margin-top: 20px;">
		<div class="offset-md-2 col-md-8">
			<table class="table table-striped table-dark">
				<thead>
					<tr>
						<th scope="col"> Contact Date</th>
						<th scope="col"> Email</th>
						<th scope="col"> Name </th>
						<th scope="col"> About </th>
					</tr>
				</thead>
				<tbody>
				@foreach ($interestedParties as $party)
					<tr>
						<td> {{ $party->created_at }} </td>
						<td> {{ $party->email}}  </td>
						<td> {{ $party->name }} </td>
						<td> <textarea cols="40" rows="5" readonly> {{ $party->about }} </textarea> </td>
					</tr>	
				@endforeach
				</tbody>
		</table>
		</div>
	</div>

</div>
@endsection
