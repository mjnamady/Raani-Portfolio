{{--
    "Get in touch" heading, text and contact form (HOME-11), shared by the About and Contact pages.
    Text comes from the view composer (sections row "contact.get_in_touch", edited on the admin Contact Page);
    the form (frontend.partials.contact_form) saves to the admin inbox.
    $spacing: optional section gap class; defaults to the bottom gap.
--}}
<section class="get-in-touch-area {{ $spacing ?? 'tmp-section-gapBottom' }}">
    <div class="container">
        <div class="contact-get-in-touch-wrap">
            <div class="get-in-touch-wrapper tmponhover">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-5">
                        <div class="section-head text-align-left">
                            @if (filled($touchEyebrow))
                                <div class="section-sub-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                                    <span class="subtitle">{{ $touchEyebrow }}</span>
                                </div>
                            @endif
                            <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">{{ $touchTitle }}</h2>
                            @if (filled($touchText))
                                <p class="description tmp-scroll-trigger tmp-fade-in animation-order-3">{{ $touchText }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="contact-inner">
                            <div class="contact-form">
                                @include('frontend.partials.contact_form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
