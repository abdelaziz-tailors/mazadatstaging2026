<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title>Oun</title>

    <link rel="shortcut icon" href="{{asset('assets/img/logo.png')}}" type="image/x-icon')}}">

    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/select2.min.css')}}">


    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/all.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/feather.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/owl.carousel.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/aos.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/my_style.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/locale_'.LaravelLocalization::getCurrentLocale().'.css')}}">
</head>
<body>

<div class="main-wrapper">
    @include('front.includes.header')

    @yield('content')


    @include('front.includes.footer')

    <div class="mouse-cursor cursor-outer"></div>
    <div class="mouse-cursor cursor-inner"></div>

</div>


<div class="progress-wrap active-progress">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewbox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919px, 307.919px; stroke-dashoffset: 228.265px;"></path>
    </svg>
</div>


{{--<script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>--}}
<script src="{{asset('assets/js/jquery-3.6.4.min.js')}}"></script>


<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

<script src="{{asset('assets/js/feather.min.js')}}"></script>

<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>

<script src="{{asset('assets/js/owl.carousel.min.js')}}"></script>

<script src="{{asset('assets/js/slick.js')}}"></script>

<script src="{{asset('assets/js/aos.js')}}"></script>

{{--<script src="{{asset('assets/js/counter.js')}}"></script>--}}
<script src="{{asset('assets/js/select2.min.js')}}"></script>

<script src="{{asset('assets/js/backToTop.js')}}"></script>

<script src="{{asset('assets/js/script.js')}}"></script>

@yield('js')

</body>


</html>
