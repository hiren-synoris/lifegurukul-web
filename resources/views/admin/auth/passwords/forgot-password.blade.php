@extends('admin.layouts.authentication')
@section('auth_title', 'Forgot Password')
@section('authentication')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ url('backoffice/login') }}" class="h1">{{ config('app.name') }}</a>
        </div>
        <div class="card-body">
            @if(!$errors->any())
            <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>
            @else
            <div class="alert alert-danger text-white d-flex" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $key => $value)
                    <li class="float-left" style="color: #FFFFFF;">{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success text-white d-flex" role="alert">
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ url('backoffice/forgot-password') }}" method="POST">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Request new password</button>
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

