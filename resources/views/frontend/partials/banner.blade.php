@if ($banner && $banner->is_active)
<div class="rpp-banner-five-area">
    <div class="container">
        <div class="banner-five-main-wrapper">
            <div class="inner">
                <h1 class="title tmp-scroll-trigger tmp-fade-in animation-order-1 tmp-title-split">{{ $banner->greeting }} {{ $banner->name }} <br> {{ $banner->role }}</h1>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2">
                    <div class="bg-benner-img-five">
                        <img class="tmp-scroll-trigger tmp-zoom-in animation-order-1" src="{{ $banner->image_url }}" alt="{{ $banner->image_alt }}" width="{{ $banner->image ? \App\Models\BannerSection::IMAGE_WIDTH : 459 }}" height="{{ $banner->image ? \App\Models\BannerSection::IMAGE_HEIGHT : 579 }}" fetchpriority="high">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 order-lg-1">
                    <div class="banner-left-content">
                        @if ($stats->isNotEmpty())
                            <div class="banner-counter">
                                <ul>
                                    @foreach ($stats as $stat)
                                        <li>
                                            <div class="banner-counter-card tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                                                <h4 class="counter title"><span class="odometer" data-count="{{ $stat->value }}">00</span>{{ $stat->suffix }}
                                                </h4>
                                                <p class="para">{{ $stat->label }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 order-lg-3">
                    <div class="banner-right-content">
                        <div class="banner-contact-info">
                            <div class="contact-info tmp-scroll-trigger tmp-fade-in animation-order-1">
                                <h4 class="title">{{ $banner->biography_heading }}</h4>
                                <p class="para">{{ $banner->biography }}</p>
                            </div>
                            {{-- Site-wide contact details (GEN-2), edited on the admin Contact Page. --}}
                            @if ($contact['address'] || $contact['email'] || $contact['phone'])
                                <div class="contact-info tmp-scroll-trigger tmp-fade-in animation-order-2">
                                    <h4 class="title">{{ $banner->contact_heading }}</h4>
                                    @if ($contact['address'])
                                        <p class="para">{{ $contact['address'] }}</p>
                                    @endif
                                    @if ($contact['email'])
                                        <p class="para"><a href="{{ $contact['email_href'] }}">{{ $contact['email'] }}</a></p>
                                    @endif
                                    @if ($contact['phone'])
                                        <p class="para">
                                            @if ($contact['phone_href'])
                                                <a href="{{ $contact['phone_href'] }}">{{ $contact['phone'] }}</a>
                                            @else
                                                {{ $contact['phone'] }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="banner-shape-img-bg">
        <img src="{{asset('frontend/assets/images/banner/banner-shape-five-bg.png')}}" alt="" aria-hidden="true">
    </div>
</div>
@endif
