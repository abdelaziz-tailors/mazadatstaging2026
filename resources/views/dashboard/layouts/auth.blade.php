<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>{{ TranslationHelper::translate('site_name') }} | @yield('title')</title>
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('dashboard/img/favicon.png') }}">
        <link rel="stylesheet" href="{{asset('dashboard/css/bootstrap.min.css')}}">
        @if (LaravelLocalization::getCurrentLocaleDirection() == 'rtl')
            <link rel="stylesheet" href="{{asset('dashboard/css/bootstrap.rtl.min.css')}}">
        @endif
        <link rel="stylesheet" href="https://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
        <link rel="stylesheet" href="{{asset('dashboard/fonts/fontawesome/css/all.min.css')}}">
        <link href="https://unpkg.com/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('dashboard/css/custom.css')}}">
        @if (LaravelLocalization::getCurrentLocaleDirection() == 'rtl')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500;600;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{asset('dashboard/css/rtl.css')}}">
        @endif
    </head>
    <body>
        <div class="main-wrapper login-body">
            <div class="login-wrapper">
                <div class="container">
                    <div class="loginbox">
                        <div class="login-left">
                            <img class="img-fluid" src="{{ asset('dashboard/img/logo-white.png') }}" alt="Logo">
                        </div>
                        <!--begin::Main-->
                        @yield('content')
                        <!--end::Main-->
                    </div>
                </div>
            </div>
        </div>
        <script src="{{asset('dashboard/js/jquery-3.6.4.min.js')}}"></script>
        <script src="{{asset('dashboard/js/bootstrap.bundle.min.js')}}"></script>
        <script src="https://unpkg.com/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{asset('dashboard/js/script.js')}}"></script>
        <script src="https://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
        @if($errors->any())
            @foreach ($errors->all() as $error)
                {{ Toastr::error($error) }}
            @endforeach
        @endif
        {!! Toastr::message() !!}

    </body>
<!--end::Body-->
</html>
