@extends('layouts.app')

@section('content')

<meta-query-viewer :query="{{ json_encode($query) }}"></meta-query-viewer>

@endsection
