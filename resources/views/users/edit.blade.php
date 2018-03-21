@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-dark">
                <div class="card-header bg-dark text-light">{{ $user->name }}</div>
								<div class="card-body">
										{{ Form::open(['route' => ['users.update', $user]]) }} 
											@method('put')	
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{$user->name}}"required readonly autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{$user->email}}" readonly required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

										   <div class="form-group row">
                            <label for="quota" class="col-md-4 col-form-label text-md-right">{{ __('Quota') }} in GB</label>
                            <div class="col-md-6">
                                <input id="quota" type="number" step="0.1" min="0" max="100" class="form-control{{ $errors->has('quota') ? ' is-invalid' : '' }}" value="{{$user->quota}}"name="quota" required>

                                @if ($errors->has('quota'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('quota') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

												<div class="form-group row">
                            <label for="admin" class="col-md-4 col-form-label text-md-right">{{ __('User is an Admin') }} </label>
                            <div class="col-md-6">
																@if ($user->isAdmin)
                                	<input id="admin" type="checkbox" class="form-control{{ $errors->has('admin') ? ' is-invalid' : '' }}" name="admin" checked>
																@else	
																	<input id="admin" type="checkbox" class="form-control{{ $errors->has('admin') ? ' is-invalid' : '' }}" name="admin" >
																@endif

                                @if ($errors->has('admin'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('admin') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                       <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>

										{{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
