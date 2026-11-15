@if ($counterAreaVisible && ($highlight || $counters->isNotEmpty()))
{{-- $spacing: optional section gap class, e.g. "tmp-section-gapBottom" on the About page. --}}
<section class="counter-area {{ $spacing ?? '' }}">
    <div class="container">
        <div class="row g-5">
            @if ($highlight)
                <div class="col-12 col-lg-6 col-xl-6 col-xxl-6">
                    <div class="year-of-expariance-wrapper bg-blur-style-one tmp-scroll-trigger tmp-fade-in animation-order-1">
                        <div class="year-expariance-wrap">
                            <h2 class="counter year-number"><span class="odometer" data-count="{{ $highlight->value }}">00</span>{{ $highlight->suffix }}
                            </h2>
                            <h3 class="year-title">
                                @foreach ($highlight->labelLines() as $line)
                                    {{ $line }}@unless ($loop->last) <br> @endunless
                                @endforeach
                            </h3>
                        </div>
                        @if ($highlight->description)
                            <p class="year-para">{{ $highlight->description }}</p>
                        @endif
                    </div>
                </div>
            @endif
            @if ($counters->isNotEmpty())
                <div class="col-12 col-lg-6 col-xl-6 col-xxl-6">
                    <div class="counter-area-right-content">
                        <div class="row g-5">
                            @foreach ($counters as $counter)
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="counter-card tmponhover tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                                        <h3 class="counter counter-title"><span class="odometer" data-count="{{ $counter->value }}">00</span>{{ $counter->suffix }}
                                        </h3>
                                        <p class="counter-para">{{ $counter->label }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
