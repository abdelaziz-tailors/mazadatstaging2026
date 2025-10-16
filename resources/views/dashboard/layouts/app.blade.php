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
        <link rel="stylesheet" href="{{asset('dashboard/css/feathericon.min.css')}}">
        <link rel="stylesheet" href="{{asset('dashboard/plugins/morris/morris.css')}}">
        @stack('css')
        <link href="https://unpkg.com/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('dashboard/css/custom.css')}}">
        @if (LaravelLocalization::getCurrentLocaleDirection() == 'rtl')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500;600;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{asset('dashboard/css/rtl.css')}}">
        @endif
        @livewireStyles
        @yield('css_lib')
    </head>


</head>
<!--end::Head-->
<!--begin::Body-->
<body>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <a href="{{ url('admin') }}" class="logo">
                    <img src="{{ asset('dashboard/img/logo.png') }}" alt="Logo">
                </a>
                <a href="{{ url('admin') }}" class="logo logo-small">
                    <img src="{{ asset('dashboard/img/logo-small.png') }}" alt="Logo" width="30" height="30">
                </a>
            </div>

            <a href="javascript:void(0);" id="toggle_btn">
                <i class="fe fe-text-align-left"></i>
            </a>
            <a class="mobile_btn" id="mobile_btn">
                <i class="fa fa-bars"></i>
            </a>
            <ul class="nav user-menu">
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link"  data-bs-toggle="dropdown">
                        <img class="rounded-circle" src="{{ asset('dashboard/img/language/'.app()->getLocale().'.png') }}" width="31" alt="{{ Auth::guard('admin')->user()->name }}">
                        {{ LaravelLocalization::getCurrentLocaleNative() }}
                    </a>
                    <div class="dropdown-menu">
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @if(app()->getLocale() != $localeCode)
                                <a class="dropdown-item" href="{{ LaravelLocalization::getLocalizedURL($localeCode) }}">
                                    <img class="me-3 rounded-circle" src="{{ asset('dashboard/img/language/'.$localeCode.'.png') }}" width="31" alt="{{ Auth::guard('admin')->user()->name }}" />
                                    {{ $properties['native'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </li>
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                        <span class="user-img">
                            <img class="rounded-circle" src="{{ Storage::disk('public')->url(Auth::guard('admin')->user()->image) }}" width="31" alt="{{ Auth::guard('admin')->user()->name }}">
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="user-header">
                            <div class="avatar avatar-sm">
                                <img src="{{ Storage::disk('public')->url(Auth::guard('admin')->user()->image) }}" alt="{{ Auth::guard('admin')->user()->name }}" class="avatar-img rounded-circle" />
                            </div>
                            <div class="user-text">
                                <h6>{{ Auth::guard('admin')->user()->name }}</h6>
                                <p class="text-muted mb-0">{{ Auth::guard('admin')->user()->role }}</p>
                            </div>
                        </div>
                        <a class="dropdown-item" href="{{ route('admin.my-profile') }}">{{ TranslationHelper::translate('my_profile') }}</a>
                        <a class="dropdown-item" href="{{ route('admin.change-my-password') }}">{{ TranslationHelper::translate('change_password') }}</a>
                        <a class="dropdown-item" href="{{ route('admin.auth.logout') }}">{{ TranslationHelper::translate('logout') }}</a>
                    </div>
                </li>
            </ul>
        </div>



        @if(Auth::guard('admin')->user()->type=="partner")
            @include('dashboard.layouts.sidebar_partner')
        @else
            @include('dashboard.layouts.sidebar')

        @endif

        <div class="page-wrapper">
            <div class="content container-fluid">
                @yield('content')
            </div>
        </div>

    </div>


    <script src="{{asset('dashboard/js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{asset('dashboard/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{asset('dashboard/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{asset('dashboard/plugins/raphael/raphael.min.js') }}"></script>
    <script src="{{asset('dashboard/plugins/morris/morris.min.js') }}"></script>
    <script src="{{asset('dashboard/js/chart.morris.js') }}"></script>
    <script src="https://unpkg.com/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{csrf_token()}}"
            }
        });
    </script>

    @livewireScripts
    @yield('scripts_lib')

    <script src="{{asset('dashboard/js/script.js') }}"></script>
    <script src="https://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    @if($errors->any())
        @foreach ($errors->all() as $error)
            {{ Toastr::error($error) }}
        @endforeach
    @endif
    {!! Toastr::message() !!}


    {{-- <script src="assets/plugins/morris/morris.min.js"></script>
    <script src="assets/js/chart.morris.js"></script> --}}
</body>
<!--end::Body-->
</html>
