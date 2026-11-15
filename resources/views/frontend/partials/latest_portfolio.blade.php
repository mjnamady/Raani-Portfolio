{{-- The background wrapper spans "Latest Portfolio" and "My Skill", as in the theme. --}}
<div class="tpm-custom-box-bg">
    @if ($portfolioVisible && $portfolioProjects->isNotEmpty())
        <section class="latest-portfolio-area custom-column-grid">
            <h2 class="latest-portfolio-custom-title end">latest-portfolio</h2>
            <div class="container">
                <div class="row">
                    @foreach ($portfolioProjects as $project)
                        @php
                            $url = route('projects.show', $project->slug);
                            $alt = $project->card_image_alt ?: $project->title;
                        @endphp
                        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="latest-portfolio-card v5 {{ $loop->even ? 'mt--40' : '' }} tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                                <div class="portfoli-card-img">
                                    <div class="img-box v2">
                                        <a href="{{ $url }}" tabindex="-1" aria-hidden="true">
                                            @if ($project->card_image_url)
                                                <img class="img-primary hidden-on-mobile" src="{{ $project->card_image_url }}" alt="{{ $alt }}" width="{{ \App\Models\Project::CARD_WIDTH }}" height="{{ \App\Models\Project::CARD_HEIGHT }}" loading="lazy">
                                                <img class="img-secondary" src="{{ $project->card_image_url }}" alt="" width="{{ \App\Models\Project::CARD_WIDTH }}" height="{{ \App\Models\Project::CARD_HEIGHT }}" loading="lazy">
                                            @endif
                                        </a>
                                    </div>
                                    <a href="{{ $url }}" class="img-link-icon" aria-label="View project: {{ $project->title }}"><i
                        class="fa-solid fa-arrow-up-long"></i></a>
                                </div>
                                <div class="portfolio-card-content-wrap">
                                    <div class="content-left">
                                        <h3 class="portfolio-card-title"><a href="{{ $url }}">{{ $project->title }}</a>
                                        </h3>
                                        @if ($project->tagline)
                                            <p class="portfoli-card-para">{{ $project->tagline }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        <!-- Tpm Our Latest Portfolio  Area End -->
    @endif

    @include('frontend.partials.my_skill')
</div>
