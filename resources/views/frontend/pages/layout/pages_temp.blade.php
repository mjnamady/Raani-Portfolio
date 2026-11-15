<!DOCTYPE html>
<html lang="en">
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
    <!-- custom css -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">
    <!-- overrides (the theme files above stay untouched) -->
    <link rel="stylesheet" href="{{ asset('frontend/custom/custom.css') }}">
</head>

<body class="project inner">

   {{-- @include('frontend.pages.layout.header') --}}
   @include('frontend.partials.header')

   
   @yield('d-content')


    {{-- @include('frontend.pages.layout.footer') --}}
    @include('frontend.partials.footer')



    @include('frontend.partials.chat_popup')
    <!-- progress area start -->
    <div class="scrollToTop" style="display: block;">
        <div class="arrowUp">
            <i class="fa-light fa-arrow-up"></i>
        </div>
        <div class="water" style="transform: translate(0px, 87%);">
            <svg viewBox="0 0 560 20" class="water_wave water_wave_back">
                <use xlink:href="#wave"></use>
            </svg>
            <svg viewBox="0 0 560 20" class="water_wave water_wave_front">
                <use xlink:href="#wave"></use>
            </svg>
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 560 20" style="display: none;">
                <symbol id="wave">
                    <path d="M420,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C514,6.5,518,4.7,528.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H420z" fill="#"></path>
                    <path d="M420,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C326,6.5,322,4.7,311.5,2.7C304.3,1.4,293.6-0.1,280,0c0,0,0,0,0,0v20H420z" fill="#"></path>
                    <path d="M140,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C234,6.5,238,4.7,248.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H140z" fill="#"></path>
                    <path d="M140,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C46,6.5,42,4.7,31.5,2.7C24.3,1.4,13.6-0.1,0,0c0,0,0,0,0,0l0,20H140z" fill="#"></path>
                </symbol>
            </svg>

        </div>
    </div>

    <script src="{{ asset('frontend/assets/js/vendor/jquery.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/vendor/waypoints.min.js') }}"></script>

    <script src="{{ asset('frontend/assets/js/plugins/odometer.js') }}"></script>
    {{-- main.js is loaded once, at the end: loading it twice initialised the sliders twice. --}}


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
    <!-- custom Js -->
    <script src="{{ asset('frontend/assets/js/main.js') }}"></script>
    <!-- overrides -->
    <script src="{{ asset('frontend/custom/custom.js') }}"></script>


</body>
</html>