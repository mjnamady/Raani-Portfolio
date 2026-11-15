@extends('frontend.pages.layout.pages_temp')
@section('title', 'Projects')
@section('d-content')
        <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">My Project</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item active">{{ $activeCategory?->name ?? 'My All Project' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->


    <!-- tmp Latest Portfolio Start -->
    <section class="latest-portfolio-area custom-column-grid tmp-section-gap">
        <div class="container">
            <div class="latest-portfolio-tabs-area">
                {{-- The tabs keep the theme's look but are real filter links, so filtering works with pagination and without JavaScript (PRJ-2). --}}
                @if ($categories->isNotEmpty())
                    <nav aria-label="Filter projects by category">
                        <ul class="nav nav-tabs" id="nav-tab">
                            <li>
                                <a class="nav-link {{ $activeCategory ? '' : 'active' }}" href="{{ route('projects.index') }}" @unless ($activeCategory) aria-current="page" @endunless>All</a>
                            </li>
                            @foreach ($categories as $category)
                                <li>
                                    <a class="nav-link {{ $activeCategory?->is($category) ? 'active' : '' }}" href="{{ route('projects.index', ['category' => $category->slug]) }}" @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <div class="tab-content bg-blur-style-one" id="nav-tabContent">
                    <div class="tab-pane fade show active">
                        @if ($projects->isEmpty())
                            <p class="text-center py-5 mb-0">No projects to show yet. Please check back soon.</p>
                        @else
                            <div class="row">
                                @foreach ($projects as $project)
                                    <div class="col-lg-6">
                                        <div class="latest-portfolio-card-style-two image-box-hover tmp-scroll-trigger tmp-fade-in animation-order-{{ ($loop->index % 4) + 1 }}">
                                            <div class="portfoli-card-img">
                                                <div class="img-box v2">
                                                    <a class="tmp-scroll-trigger tmp-zoom-in" href="{{ route('projects.show', $project->slug) }}">
                                                        @if ($project->card_image_url)
                                                            <img class="w-100" src="{{ $project->card_image_url }}" alt="{{ $project->card_image_alt ?: $project->title }}" width="{{ \App\Models\Project::CARD_WIDTH }}" height="{{ \App\Models\Project::CARD_HEIGHT }}" loading="lazy">
                                                        @endif
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="portfolio-card-content-wrap">
                                                <div class="content-left">
                                                    <h3 class="portfolio-card-title"><a href="{{ route('projects.show', $project->slug) }}">{{ $project->title }}</a></h3>
                                                    @if ($project->tags->isNotEmpty())
                                                        <div class="tag-items">
                                                            <ul>
                                                                @foreach ($project->tags as $tag)
                                                                    <li>
                                                                        <a class="tag-item">{{ $tag->name }}</a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>

                                                <a class="tmp-btn hover-icon-reverse radius-round btn-border btn-md" href="{{ route('projects.show', $project->slug) }}" aria-label="View project: {{ $project->title }}">
                                                    <span class="icon-reverse-wrapper">
                                                        <span class="btn-text">View project</span>
                                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                                    </span>
                                                </a>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt--50">
                                {{ $projects->links('frontend.partials.pagination') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- tmp Latest Portfolio end -->
@endsection
