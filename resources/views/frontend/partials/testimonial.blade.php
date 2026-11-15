{{-- Testimonial slider (HOME-9, TST-1). The theme's Swiper (.swiper-testimonials-2) shows 2 slides per view and loops. --}}
@if ($testimonialsVisible && $testimonials->isNotEmpty())
<section class="testimonial-area {{ $spacing ?? 'tmp-section-gapTop' }}">
    <div class="container">
        <h2 style="font-size:180px" class="testimonial-custom-title end tmp-scroll-trigger tmp-fade-in animation-order-1">{{ $testimonialsSection?->title ?: 'Testimonial' }}</h2>
        <div class="row g-5">
            <!-- Start Single Testimonial  -->

            <div class="col-lg-12">
                <div class="swiper-testimonials-area-wrapper-card">
                    <div class="swiper swiper-testimonials-2">
                        <div class="swiper-wrapper">
                            @foreach ($testimonials as $testimonial)
                                <div class="swiper-slide">
                                    <div class="testimonial-card tmponhover style-2 tmp-scroll-trigger animation-order-{{ $loop->odd ? 1 : 2 }}">
                                        <div class="content">
                                            <div class="testimonital-icon">
                                                <img src="{{ asset('frontend/assets/images/icons/quote.svg') }}" alt="">
                                            </div>
                                            <h2 class="text-doc">{{ $testimonial->quote }}</h2>
                                            <h3 class="card-title">{{ $testimonial->name }}</h3>
                                            @if (filled($testimonial->role))
                                                <p class="card-para">{{ $testimonial->role }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- End Single Testimonial  -->

        </div>
    </div>
</section>
@endif
