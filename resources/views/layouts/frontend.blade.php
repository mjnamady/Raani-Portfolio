<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Per-page meta and Open Graph tags arrive with M7 (SEO); pages can already set these sections. --}}
    <meta name="description" content="@yield('meta_description', $siteTagline)">
    <link rel="icon" href="{{ $siteFavicon }}">
    <title>@hasSection('title')@yield('title') | @endif{{ $siteName }}</title>
    <!-- Bootstrap min css -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/vendor/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/plugins/swiper.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/plugins/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/vendor/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/vendor/bootstrap.min.css') }}">
    <!-- theme css -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">
    <!-- overrides (the theme files above stay untouched) -->
    <link rel="stylesheet" href="{{ asset('frontend/custom/custom.css') }}">
    @stack('styles')
</head>

<body class="@yield('body_class', 'color-primary-2nd')">

    <!-- tpm-header-area start -->
    @include('frontend.partials.header')

    @yield('content')

    <!-- Start Footer Area  -->
    @include('frontend.partials.footer')
    <!-- End Footer Area  -->

    @include('frontend.partials.chat_popup')

    @include('frontend.partials.back_to_top')

    <script src="{{ asset('frontend/assets/js/vendor/jquery.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/waypoints.min.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/plugins/odometer.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/appear.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/vendor/jquery-one-page-nav.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/swiper.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/plugins/gsap.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/splittext.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/scrolltigger.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/scrolltoplugins.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/smoothscroll.js') }}"></script>
    <!-- bootstrap Js-->
    <script src="{{ asset('frontend/assets/js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/waw.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/isotop.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/animation.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/contact.form.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/backtop.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/plugins/text-type.js') }}"></script>
    <!-- theme Js -->
    <script src="{{ asset('frontend/assets/js/main.js') }}"></script>
    <!-- overrides -->
    <script src="{{ asset('frontend/custom/custom.js') }}"></script>
    @stack('scripts')

</body>
</html>
