@extends('layouts.app')
@section('content')

		<form  action="{{ route('queries.destroy', ['id' => $query->id]) }}" method="POST" style="display: none;" id='deleteQuery'>
			@method('delete')
			@csrf
	 </form>

		<query-viewer :query="{{ json_encode($query) }}" delete-form-id="deleteQuery"></query-viewer>
@endsection
