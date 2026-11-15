@if ($services->isNotEmpty())
<section class="service-area tmp-section-gap" id="services">
    <div class="container">
        <div class="row justify-content-center">
            @foreach ($services as $service)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="service-card-v1 tmp-scroll-trigger tmp-fade-in animation-order-{{ ($loop->index % 4) + 1 }} tmp-link-animation">
                        <div class="service-card-icon">
                            <i class="{{ $service->icon }}" aria-hidden="true"></i>
                        </div>
                        <h4 class="service-title">
                            @if (Route::has('services.show'))
                                <a href="{{ route('services.show', $service->slug) }}">{{ $service->title }}</a>
                            @else
                                <a href="#services" aria-disabled="true">{{ $service->title }}</a>
                            @endif
                        </h4>
                        <p class="service-para">{{ number_format($service->project_count) }} {{ $service->project_count_label }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
