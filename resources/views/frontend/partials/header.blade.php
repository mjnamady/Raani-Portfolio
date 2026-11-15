{{-- Header, desktop menu panel and mobile menu. Logo, social links, panel text and contact details come from Site Settings (M3). --}}
 <header class="tmp-header-area-start header-one header--sticky header--transparent">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-content">
                        <div class="logo">
                            <a href="{{ url('/') }}">
                                <img class="logo-dark" src="{{ $siteLogo }}" alt="{{ $siteName }}">
                                <img class="logo-white" src="{{ $siteLogoLight }}" alt="{{ $siteName }}">
                            </a>
                        </div>
                        <nav class="tmp-mainmenu-nav d-none d-xl-block">
                            <ul class="tmp-mainmenu">
                                <li>
                                    <a href="{{ url('/') }}">Home</a>
                                </li>
                                <li>
                                    <a href="{{ route('about.index') }}">About</a>
                                </li>
                                {{-- Services dropdown returns with the services pages (GEN-4). --}}
                                <li>
                                    <a href="{{ route('blog.index') }}">Blog</a>
                                </li>
                                <li>
                                    <a href="{{ route('projects.index') }}">Projects</a>
                                </li>
                                <li>
                                    <a href="{{ route('contact.index') }}">Contact</a>
                                </li>
                            </ul>

                        </nav>
                        <div class="tmp-header-right">
                            @if ($socialLinks !== [])
                                <div class="social-share-wrapper d-none d-md-block">
                                    @include('frontend.partials.social_links')
                                </div>
                            @endif
                            <div class="actions-area">
                                <div class="tmp-side-collups-area d-none d-xl-block">
                                    <button class="tmp-menu-bars tmp_button_active" aria-label="Open the menu panel"><i class="fa-regular fa-bars-staggered"></i></button>
                                </div>
                                <div class="tmp-side-collups-area d-block d-xl-none">
                                    <button class="tmp-menu-bars humberger_menu_active" aria-label="Open the menu"><i class="fa-regular fa-bars-staggered"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- tpm-header-area end -->

    <div class="d-none d-xl-block">
        <div class="tmp-sidebar-area tmp_side_bar">
            <div class="inner">
                <div class="top-area">
                    <a href="{{ url('/') }}" class="logo">
                        <img class="logo-dark" src="{{ $siteLogo }}" alt="{{ $siteName }}">
                        <img class="logo-white" src="{{ $siteLogoLight }}" alt="{{ $siteName }}">
                    </a>
                    <div class="close-icon-area">
                        <button class="tmp-round-action-btn close_side_menu_active" aria-label="Close the menu panel">
                            <i class="fa-sharp fa-light fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="content-wrapper">
                    <div class="image-area-feature">
                        <a href="{{ route('about.index') }}">
                            <img src="{{ $offcanvas['image_url'] }}" alt="{{ $siteName }}">
                        </a>
                    </div>
                    @if (filled($offcanvas['heading']))
                        <h5 class="title mt--30">{{ $offcanvas['heading'] }}</h5>
                    @endif
                    @if (filled($offcanvas['bio']))
                        <p class="disc">{{ $offcanvas['bio'] }}</p>
                    @endif
                    @if ($contact['phone'] || $contact['email'] || $contact['address'])
                        <div class="short-contact-area">
                            @if ($contact['phone'])
                                <div class="single-contact">
                                    <i class="fa-solid fa-phone"></i>
                                    <div class="information tmp-link-animation">
                                        <span>{{ $offcanvas['phone_label'] }}</span>
                                        @if ($contact['phone_href'])
                                            <a href="{{ $contact['phone_href'] }}" class="number">{{ $contact['phone'] }}</a>
                                        @else
                                            <span class="number">{{ $contact['phone'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($contact['email'])
                                <div class="single-contact">
                                    <i class="fa-solid fa-envelope"></i>
                                    <div class="information tmp-link-animation">
                                        <span>{{ $offcanvas['email_label'] }}</span>
                                        <a href="{{ $contact['email_href'] }}" class="number">{{ $contact['email'] }}</a>
                                    </div>
                                </div>
                            @endif

                            @if ($contact['address'])
                                <div class="single-contact">
                                    <i class="fa-solid fa-location-crosshairs"></i>
                                    <div class="information tmp-link-animation">
                                        <span>{{ $offcanvas['address_label'] }}</span>
                                        <span class="number">{{ $contact['address'] }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if ($socialLinks !== [])
                        <!-- social area start -->
                        <div class="social-wrapper mt--20">
                            <span class="subtitle">{{ $offcanvas['social_label'] }}</span>
                            @include('frontend.partials.social_links')
                        </div>
                        <!-- social area end -->
                    @endif
                </div>
            </div>
        </div>
        <a class="overlay_close_side_menu close_side_menu_active" href="javascript:void(0);" aria-label="Close the menu panel"></a>
    </div>

    <div class="d-block d-xl-none">
        <div class="tmp-popup-mobile-menu">
            <div class="inner">
                <div class="header-top">
                    <div class="logo">
                        <a href="{{ url('/') }}" class="logo-area">
                            <img class="logo-dark" src="{{ $siteLogo }}" alt="{{ $siteName }}">
                            <img class="logo-white" src="{{ $siteLogoLight }}" alt="{{ $siteName }}">
                        </a>

                    </div>
                    <div class="close-menu">
                        <button class="close-button tmp-round-action-btn" aria-label="Close the menu">
                            <i class="fa-sharp fa-light fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <ul class="tmp-mainmenu">
                    <li>
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('about.index') }}">About</a>
                    </li>
                    <li>
                        <a href="{{ route('blog.index') }}">Blog</a>
                    </li>
                    <li>
                        <a href="{{ route('projects.index') }}">Projects</a>
                    </li>
                    <li>
                        <a href="{{ route('contact.index') }}">Contact</a>
                    </li>
                </ul>

                @if ($socialLinks !== [])
                    <div class="social-wrapper mt--40">
                        <span class="subtitle">{{ $offcanvas['social_label'] }}</span>
                        @include('frontend.partials.social_links')
                    </div>
                    <!-- social area end -->
                @endif

            </div>
        </div>
    </div>
