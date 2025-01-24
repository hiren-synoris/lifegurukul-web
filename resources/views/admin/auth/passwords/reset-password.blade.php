@extends('admin.layouts.authentication')
@section('auth_title', 'Reset Password link for Lifegurukul')
@section('authentication')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ url('backoffice/login') }}" class="h1">{{ config('app.name') }}</a>
        </div>
        <div class="card-body">
            @if(!$errors->any())
            <p class="login-box-msg">Reset Password</p>
            @else
            <div class="alert alert-danger text-white d-flex" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $key => $value)
                    <li class="float-left" style="color: #FFFFFF;">{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
             @endif
            <form action="{{ url('backoffice/forgot-password/'.$id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    {{-- @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                     @enderror --}}

                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Set password</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <p class="mt-3 mb-1">
                <a href="{{ url('backoffice/login') }}">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection

