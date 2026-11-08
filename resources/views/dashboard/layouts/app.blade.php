<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        @include('dashboard.partials.theme-init')
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
        <link rel="stylesheet" href="{{asset('dashboard/plugins/apex/apexcharts.css')}}">
        @stack('css')
        <link href="https://unpkg.com/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('dashboard/css/custom.css')}}">
        @if (LaravelLocalization::getCurrentLocaleDirection() == 'rtl')
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;500;600;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{asset('dashboard/css/rtl.css')}}?v={{ @filemtime(public_path('dashboard/css/rtl.css')) }}">
        @endif
        <link rel="stylesheet" href="{{asset('dashboard/css/theme.css')}}?v={{ @filemtime(public_path('dashboard/css/theme.css')) }}">
        @livewireStyles
        @yield('css_lib')
        @include('dashboard.partials.workspace-assets')
    </head>


<!--end::Head-->
<!--begin::Body-->
<body class="md-workspace">
    <a class="md-skip-link" href="#main-content">{{ __('dashboard-ui.skip_content') }}</a>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <a href="{{ url('admin') }}" class="logo">
                    <img src="{{ asset('dashboard/img/logo.png') }}" alt="{{ TranslationHelper::translate('site_name') }}">
                </a>
                <a href="{{ url('admin') }}" class="logo logo-small">
                    <img src="{{ asset('dashboard/img/logo-small.png') }}" alt="{{ TranslationHelper::translate('site_name') }}" width="30" height="30">
                </a>
            </div>

            <button type="button" id="toggle_btn" class="md-sidebar-toggle" aria-controls="sidebar" aria-expanded="true" aria-label="{{ __('dashboard-ui.toggle_navigation') }}" title="{{ __('dashboard-ui.toggle_navigation') }}">
                <i class="fa-solid fa-angles-right md-sidebar-hide-icon"></i>
                <i class="fa-solid fa-angles-left md-sidebar-show-icon"></i>
            </button>
            <button type="button" class="mobile_btn" id="mobile_btn" aria-controls="sidebar" aria-expanded="false" aria-label="{{ __('dashboard-ui.open_navigation') }}">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
            <a class="md-mobile-brand" href="{{ route('admin.dashboard.index') }}">{{ TranslationHelper::translate('site_name') }}</a>

            <form action="{{ route('admin.search.index') }}" method="GET" class="md-header-search" role="search" id="workspace-search">
                <button type="submit" aria-label="{{ __('dashboard-ui.search') }}"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" aria-label="{{ __('dashboard-ui.search') }}"
                    placeholder="{{ TranslationHelper::translate('dashboard_search_placeholder') }}">
                <kbd aria-hidden="true">Ctrl K</kbd>
            </form>

            <ul class="nav user-menu">
                <li>@include('dashboard.partials.theme-control')</li>
                <li class="md-mobile-search-item"><button type="button" class="md-mobile-search" aria-controls="workspace-search" aria-expanded="false" aria-label="{{ __('dashboard-ui.search') }}"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button></li>
                {{-- No language switcher here: SetDashboardLocale middleware force-redirects
                     every /admin request back to config('app.dashboard_locale') ('ar') on
                     every request, so the dashboard has no real working multi-language
                     support to switch between — a switcher would just silently do nothing. --}}
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link md-notif-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ TranslationHelper::translate('notifications') }}">
                        <span class="md-notif-icon-wrap">
                            <i class="fa-solid fa-bell"></i>
                            @if(($headerPendingReviewCount ?? 0) > 0)
                                <span class="md-notif-badge">{{ $headerPendingReviewCount }}</span>
                            @endif
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end md-notif-menu">
                        <div class="user-header">
                            <h6 class="mb-0">{{ TranslationHelper::translate('notifications') }}</h6>
                        </div>
                        @forelse (($headerNotifications ?? collect()) as $notification)
                            <a class="dropdown-item md-notif-item" href="{{ $notification['url'] }}">
                                <span class="stat-icon stat-icon-{{ $notification['color'] }} md-notif-item-icon">
                                    <i class="{{ $notification['icon'] }}"></i>
                                </span>
                                <span class="md-notif-item-body">
                                    <span class="md-notif-item-title">{{ $notification['title'] }}</span>
                                    <span class="md-notif-item-desc">{{ $notification['description'] }}</span>
                                </span>
                                <span class="md-notif-item-time">{{ $notification['created_at']?->diffForHumans() }}</span>
                            </a>
                        @empty
                            <p class="text-muted text-center mb-0 px-3 py-3">{{ TranslationHelper::translate('no_notifications') }}</p>
                        @endforelse
                    </div>
                </li>
                <li class="nav-item dropdown has-arrow">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ TranslationHelper::translate('my_profile') }}: {{ Auth::guard('admin')->user()->name }}">
                        @include('dashboard.partials.avatar', ['path' => Auth::guard('admin')->user()->image, 'name' => Auth::guard('admin')->user()->name, 'size' => 31])
                        <span class="md-account-copy ms-2 d-none d-lg-inline">
                            <span class="fw-semibold">{{ Auth::guard('admin')->user()->name }}</span>
                            <small>{{ Auth::guard('admin')->user()->type === 'partner' ? __('dashboard-ui.partner_workspace') : __('dashboard-ui.admin_workspace') }}</small>
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="user-header">
                            @include('dashboard.partials.avatar', ['path' => Auth::guard('admin')->user()->image, 'name' => Auth::guard('admin')->user()->name, 'size' => 40])
                            <div class="user-text">
                                <h6 class="mb-1">{{ Auth::guard('admin')->user()->name }}</h6>
                                <p class="text-muted mb-0">{{ ucfirst(Auth::guard('admin')->user()->role) }}</p>
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
            @include('dashboard.layouts.sidebar_subscriber')
        @else
            @include('dashboard.layouts.sidebar')

        @endif

        <div class="page-wrapper">
            <main class="content container-fluid" id="main-content" tabindex="-1">
                @include('dashboard.partials.validation-summary')
                @yield('content')
            </main>
            <footer class="md-dashboard-footer">
                <span><span class="md-status-dot" aria-hidden="true"></span> {{ Auth::guard('admin')->user()->type === 'partner' ? __('dashboard-ui.partner_workspace') : __('dashboard-ui.admin_workspace') }}</span>
                <span>{{ TranslationHelper::translate('site_name') }} &copy; {{ now()->year }}</span>
            </footer>
        </div>

    </div>


    <script src="{{asset('dashboard/js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{asset('dashboard/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{asset('dashboard/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
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
    @stack('scripts')
    <script src="{{ asset('dashboard/js/workspace.js') }}?v={{ filemtime(public_path('dashboard/js/workspace.js')) }}" defer></script>


    {{-- <script src="assets/plugins/morris/morris.min.js"></script>
    <script src="assets/js/chart.morris.js"></script> --}}
</body>
<!--end::Body-->
</html>
