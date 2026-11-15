{{-- Footer and copyright bar. Logo, tagline, quick links, contact details, social links and copyright come from Site Settings (M3). --}}
 <footer class="footer-area footer-style-one-wrapper  tmp-section-gap">
    <div class="container">
        <div class="footer-main footer-style-one">
            <div class="row g-5">
                <div class="col-lg-5 col-md-6">
                    <div class="single-footer-wrapper border-right mr--20">
                        <div class="logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}">
                            </a>
                        </div>
                        @if (filled($footer['tagline_highlight']) || filled($footer['tagline']))
                            <p class="description">@if (filled($footer['tagline_highlight']))<span>{{ $footer['tagline_highlight'] }}</span>@endif {{ $footer['tagline'] }}</p>
                        @endif
                        {{-- The newsletter field returns when sign-ups are in scope (MVP §2.2). --}}
                    </div>
                </div>
                @if ($footer['quick_links'] !== [])
                    <div class="col-lg-4 col-md-6">
                        <div class="single-footer-wrapper quick-link-wrap">
                            <h5 class="ft-title">{{ $footer['links_title'] }}</h5>
                            <ul class="ft-link tmp-link-animation">
                                @foreach ($footer['quick_links'] as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" @if ($link['external']) target="_blank" rel="noopener" @endif>{{ $link['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="col-lg-3 col-md-6">
                    <div class="single-footer-wrapper contact-wrap">
                        @if ($contact['email'] || $contact['address'] || $contact['phone'])
                            <h5 class="ft-title">{{ $footer['contact_title'] }}</h5>
                            <ul class="ft-link tmp-link-animation">
                                @if ($contact['email'])
                                    <li><span class="ft-icon"><i class="fa-solid fa-envelope"></i></span><a href="{{ $contact['email_href'] }}">{{ $contact['email'] }}</a></li>
                                @endif
                                @if ($contact['address'])
                                    <li><span class="ft-icon"><i class="fa-solid fa-location-dot"></i></span>{{ $contact['address'] }}</li>
                                @endif
                                @if ($contact['phone'])
                                    <li><span class="ft-icon"><i class="fa-solid fa-phone"></i></span>
                                        @if ($contact['phone_href'])
                                            <a href="{{ $contact['phone_href'] }}">{{ $contact['phone'] }}</a>
                                        @else
                                            {{ $contact['phone'] }}
                                        @endif
                                    </li>
                                @endif
                            </ul>
                        @endif
                        @include('frontend.partials.social_links', ['class' => 'footer'])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bg-img">
        <img src="{{ asset('frontend/assets/images/footer/footer-bg-img.png') }}" alt="" aria-hidden="true">
    </div>
</footer>

<!-- Start Copyright-area-one Area  -->
<div class="copyright-area-one">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-wrapper">
                    <p class="copy-right-para">{{ $footer['copyright'] }}</p>
                    <ul>
                        {{-- Terms and Privacy links return with the legal pages (M7). --}}
                        <li><a href="{{ route('contact.index') }}">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
