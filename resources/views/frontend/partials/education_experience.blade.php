@php
    $showEducation = ($educationSection?->is_active ?? true) && $educationEntries->isNotEmpty();
    $showExperience = ($experienceSection?->is_active ?? true) && $experienceEntries->isNotEmpty();
    $experienceImage = \App\Models\ResumeEntry::DEFAULT_EXPERIENCE_IMAGE;
@endphp

@if ($showEducation || $showExperience)
{{-- $spacing: optional section gap class; the home page uses the top gap. --}}
<section class="education-experience {{ $spacing ?? 'tmp-section-gapTop' }}">
    <div class="container">
        @if ($showEducation)
            <h2 class="custom-title mb-32 tmp-scroll-trigger tmp-fade-in animation-order-1">{{ $educationSection?->title ?? 'Education' }} <span><img
                        src="{{ asset('frontend/assets/images/custom-line/custom-line.png') }}" alt="" aria-hidden="true"></span>
            </h2>
            <div class="row g-5">
                @foreach ($educationEntries as $entry)
                    <div class="col-lg-6 col-sm-6">
                        <div class="education-experience-card tmponhover tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                            <h4 class="edu-sub-title">{{ $entry->educationHeading() }}</h4>
                            <h2 class="edu-title">{{ $entry->period }}</h2>
                            <p class="edu-para">{{ $entry->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($showExperience)
            <div class="experiences-wrapper v2">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="experiences-wrap-right-content">
                            <img class="tmp-scroll-trigger tmp-zoom-in animation-order-1"
                                 src="{{ $experienceSection ? $experienceSection->imageUrl($experienceImage) : asset($experienceImage) }}"
                                 alt="{{ $experienceSection?->image_alt }}"
                                 width="{{ \App\Models\ResumeEntry::IMAGE_WIDTH }}"
                                 height="{{ \App\Models\ResumeEntry::IMAGE_HEIGHT }}"
                                 loading="lazy">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="experiences-wrap-left-content">

                            <h2 class="custom-title mb-32 tmp-scroll-trigger tmp-fade-in animation-order-1">{{ $experienceSection?->title ?? 'Experiences' }} <span><img
                            src="{{ asset('frontend/assets/images/custom-line/custom-line.png') }}" alt="" aria-hidden="true"></span></h2>

                            @foreach ($experienceEntries as $entry)
                                <div class="experience-content tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                                    <p class="ex-subtitle">{{ $entry->experienceLabel() }}</p>
                                    <h2 class="ex-name">{{ $entry->organisation }}</h2>
                                    <h3 class="ex-title">{{ $entry->role }}</h3>
                                    <p class="ex-para">{{ $entry->description }}</p>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endif
