<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>403</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/style.css') }}">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center error-page-area">
            <div class="col-8 error-inner">
                <div class="error-box">
                    @if(!empty(config('settings.logo')))
                        <div class="error-logo">
                            <a href="{{ request()->is('backoffice/*') ? route('dashboard') : route('home') }}">
                                <img src="{{ Storage::url(config('settings.logo'))}}" class="img-fluid" alt="Logo">
                            </a>
                        </div>
                    @endif
                    <div class="error-box-img">
                        <img src="{{ URL::asset('/front/img/error-01.png') }} " alt="Error Code Image" class="img-fluid">
                    </div>
                    <h3 class="h2 mb-3"> Oh No! Error 403</h3>
                    <h4 class="h2 font-weight-normal">Permission Denied</h4>
                </div>
            </div>
        </div>
    </div>
</body>
</html>