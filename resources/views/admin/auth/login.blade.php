@extends('admin.layouts.authentication')
@section('auth_title', 'Log In')
@section('authentication')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('backoffice/login') }}"><img src="{{ !empty(config('settings.logo')) ? Storage::url(config('settings.logo')) : logo_default() }}" alt="{{env('APP_NAME');}}" class="img-fluid"></a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            @if(!$errors->any())
                <p class="login-box-msg">Sign in to start your session</p>
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

            <form action="{{ url('backoffice/login') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" class="form-control" placeholder="Email"  value="{{ old('email') }}" name="email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" placeholder="Password" value="{{ old('password') }}" name="password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember">
                            <label for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            {{-- <div class="social-auth-links text-center mb-3">
                <p>- OR -</p>
                <a href="#" class="btn btn-block btn-primary">
                    <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
                </a>
                <a href="#" class="btn btn-block btn-danger">
                    <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
                </a>
            </div> --}}
            <!-- /.social-auth-links -->

            <p class="mb-1">
                <a href="{{ url('backoffice/forgot-password') }}">I forgot my password</a>
            </p>
            {{-- <p class="mb-0">
                <a href="{{ url('backoffice/register') }}" class="text-center">Register</a>
            </p> --}}
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@endsection



