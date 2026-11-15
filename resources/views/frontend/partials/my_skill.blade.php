{{-- "My Skill" section (HOME-4, SKL-1, SKL-2). The theme script slides the red background onto the "current" row. --}}
@if ($skillsVisible && $skills->isNotEmpty())
@php
    // One row must be "current": the highlighted skill, or the first one.
    $currentId = $skills->firstWhere('is_highlighted', true)?->id ?? $skills->first()->id;
    // Theme defaults apply only until the heading is saved; an emptied small heading stays hidden.
    $eyebrow = $skillsSection ? $skillsSection->eyebrow : 'My Skill';
    $titleLines = preg_split('/\R/', (string) ($skillsSection ? $skillsSection->title : "Elevated Designs Personalized\nthe best Experiences"));
@endphp
<!-- Tpm My Skill Area Start -->
<section class="my-skill tmp-section-gapTop">
    <div class="container">
        <div class="section-head text-align-left mb--60">
            @if (filled($eyebrow))
                <div class="section-sub-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                    <span class="subtitle">{{ $eyebrow }}</span>
                </div>
            @endif
            <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">@foreach ($titleLines as $line){{ trim($line) }}@unless ($loop->last) <br> @endunless @endforeach</h2>
        </div>
        <div class="services-widget v1">
            @foreach ($skills as $skill)
                <div class="service-item {{ $skill->id === $currentId ? 'current' : '' }} tmp-scroll-trigger tmp-fade-in animation-order-{{ min($loop->iteration, 6) }}">
                    <div class="my-skill-card">
                        <div class="card-icon">
                            <i class="{{ $skill->icon }}" aria-hidden="true"></i>
                        </div>
                        <div class="card-title">
                            <h3 class="main-title">{{ $skill->title }}</h3>
                            <p class="sub-title">{{ number_format($skill->done_count) }} {{ $skill->done_label }}</p>
                        </div>
                        <p class="card-para">{{ $skill->description }}</p>
                    </div>
                    {{-- Empty click target used by the theme's highlight script; not useful to screen readers or keyboards. --}}
                    <button type="button" class="service-link modal-popup" tabindex="-1" aria-hidden="true"></button>
                </div>
            @endforeach
            <div class="active-bg wow fadeInUp mleave"></div>
        </div>
    </div>
</section>
<!-- Tpm My Skill Area End -->
@endif
