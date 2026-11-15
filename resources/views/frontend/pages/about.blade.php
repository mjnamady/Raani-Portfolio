@extends('frontend.pages.layout.pages_temp')
@section('title', 'About')
@section('d-content')

    <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12"> 
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">About Me</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item active">About Me</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->

    <!-- Tpm Service Area Start: the home page service cards (Services Area) -->
    @include('frontend.partials.service')
    <!-- Tpm Service Area End -->

    <!-- tmp skill area start (ABT-5): managed on the admin "About Page" -->
    @if ($skillColumns->isNotEmpty())
        <div class="tmp-skill-area tmp-section-gapBottom">
            <div class="container">
                <div class="row">
                    <div class="inner">
                        <div class="row">
                            @foreach ($skillColumns as $column)
                                <div class="col-lg-6">
                                    <div class="progress-wrapper">
                                        <div class="content">
                                            <h2 class="custom-title mb--30 tmp-scroll-trigger tmp-fade-in animation-order-1">
                                                {{ $column['title'] }} <span><img src="{{ asset('frontend/assets/images/custom-line/custom-line.png') }}" alt="" aria-hidden="true"></span>
                                            </h2>
                                            @foreach ($column['bars'] as $bar)
                                                @php
                                                    // Whole number 0-100 (validated on save, clamped here), so it is safe in the style attribute.
                                                    $width = $bar->width();
                                                    $duration = number_format(0.5 + 0.1 * min($loop->index, 5), 1);
                                                    $delay = number_format(0.3 + 0.1 * min($loop->index, 5), 1);
                                                @endphp
                                                <!-- Start Single Progress Charts -->
                                                <div class="progress-charts">
                                                    <h6 class="heading heading-h6" id="skill-bar-{{ $bar->id }}">{{ $bar->name }}</h6>
                                                    <div class="progress">
                                                        <div class="progress-bar wow fadeInLeft" data-wow-duration="{{ $duration }}s" data-wow-delay="{{ $delay }}s" role="progressbar" style="width: {{ $width }}%; visibility: visible; animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s; animation-name: fadeInLeft;" aria-labelledby="skill-bar-{{ $bar->id }}" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100">
                                                            <span class="percent-label">{{ $width }}%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Single Progress Charts -->
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- tmp skill area end -->

    <!-- Tpm Counter Area Start: Counter Area (ABT-4) -->
    @include('frontend.partials.counter', ['spacing' => 'tmp-section-gapBottom'])
    <!-- Tpm Counter Area End -->

    <!-- Tpm Education Experience Area Start (ABT-2, ABT-3) -->
    @include('frontend.partials.education_experience', ['spacing' => 'tmp-section-gapBottom'])
    <!-- Tpm Education Experience Area End -->

    <!-- Tpm Testimonial Start (ABT-4) -->
    @include('frontend.partials.testimonial', ['spacing' => 'tmp-section-gapBottom'])
    <!-- Tpm Testimonial End -->

    {{-- The price plan section is deferred (MVP §2.2) and comes back once pricing is in scope. --}}

    <!-- Tpm Get In touch start: shared with the Contact page, edited on the admin "Contact Page" -->
    @include('frontend.partials.get_in_touch', ['spacing' => 'tmp-section-gapBottom'])
    <!-- Tpm Get In touch End -->

@endsection
