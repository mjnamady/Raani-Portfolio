@extends('frontend.pages.layout.pages_temp')
@section('title', $project->title)
@section('d-content')
    <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">Project Details</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item active">{{ $project->title }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->


    <div class="project-details-area-wrapper tmp-section-gap">
        <div class="container">
            <div class="row">
                @if ($project->cover_image_url)
                    <div class="col-lg-12">
                        <div class="project-details-thumnail-wrap">
                            <img src="{{ $project->cover_image_url }}" alt="{{ $project->cover_image_alt ?: $project->title }}" width="{{ \App\Models\Project::COVER_WIDTH }}" height="{{ \App\Models\Project::COVER_HEIGHT }}" fetchpriority="high">
                        </div>
                    </div>
                @endif
                <div class="col-lg-8">
                    <div class="project-details-content-wrap">
                        <h2 class="title">{{ $project->title }}</h2>

                        @if ($project->content)
                            {{-- Sanitized on save with the strict "rich_text" allow-list (App\Support\RichText). --}}
                            <div class="project-rich-content">
                                {!! $project->content !!}
                            </div>
                        @endif

                        @if (! empty($project->features))
                            <div class="check-box-wrap">
                                <ul>
                                    @foreach ($project->features as $feature)
                                        <li>
                                            <h4 class="check-box-item"><span><i
                                                        class="fa-solid fa-circle-check"></i></span>{{ $feature }}</h4>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($project->images->isNotEmpty())
                            <div class="project-details-swiper-wrapper">
                                <div class="swiper project-details-swiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($project->images as $image)
                                            <div class="swiper-slide">
                                                <div class="project-details-img">
                                                    <img src="{{ $image->url }}" alt="{{ $image->alt ?: $project->title }}" width="{{ \App\Models\ProjectImage::WIDTH }}" height="{{ \App\Models\ProjectImage::HEIGHT }}" loading="lazy">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($project->images->count() > 1)
                                    <div class="project-details-swiper-btn">
                                        <div class="project-swiper-button-prev"><span><i
                                                    class="fa-solid fa-arrow-left"></i></span>Previous</div>
                                        <div class="project-swiper-button-next">Next <span><i
                                                    class="fa-solid fa-arrow-right"></i></span></div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Previous / next project (PRJ-4) -->
                        @if ($previous || $next)
                            <nav class="project-pager" aria-label="More projects">
                                @if ($previous)
                                    <a class="tmp-btn hover-icon-reverse radius-round btn-border btn-md project-pager-prev" href="{{ route('projects.show', $previous->slug) }}">
                                        <span class="icon-reverse-wrapper">
                                            <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
                                            <span class="btn-text">{{ Str::limit($previous->title, 32) }}</span>
                                        </span>
                                    </a>
                                @endif
                                @if ($next)
                                    <a class="tmp-btn hover-icon-reverse radius-round btn-border btn-md project-pager-next" href="{{ route('projects.show', $next->slug) }}">
                                        <span class="icon-reverse-wrapper">
                                            <span class="btn-text">{{ Str::limit($next->title, 32) }}</span>
                                            <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                            <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                        </span>
                                    </a>
                                @endif
                            </nav>
                        @endif
                    </div>
                    <!-- Tpm Get In touch start -->
                    <section class="get-in-touch-area pt--80">
                        <div class="container p-0">
                            <div class="contact-get-in-touch-wrap">
                                <div class="get-in-touch-wrapper tmponhover">
                                    <div class="row g-5 align-items-center">
                                        <div class="col-lg-12">
                                            <div class="contact-inner">
                                                <div class="contact-form">
                                                    {{-- Saved to the inbox as a "project" message, linked to this project. --}}
                                                    @include('frontend.partials.contact_form', ['source' => \App\Models\Message::SOURCE_PROJECT, 'project' => $project])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- Tpm Get In touch End -->
                </div>
                <div class="col-lg-4">
                    <div class="signle-side-bar project-details-area tmponhover">
                        <div class="header">
                            <h3 class="title">Project Details</h3>
                        </div>
                        <div class="body">
                            <div class="project-details-info">Name: <span>{{ $project->title }}</span></div>
                            @if ($project->client_name)
                                <div class="project-details-info">Author: <span>{{ $project->client_name }}</span></div>
                            @endif
                            @if ($project->project_date)
                                <div class="project-details-info">Date: <span>{{ $project->project_date->format('j F, Y') }}</span></div>
                            @endif
                            @if ($project->category)
                                <div class="project-details-info">Category: <span><a href="{{ route('projects.index', ['category' => $project->category->slug]) }}">{{ $project->category->name }}</a></span></div>
                            @endif
                            @if ($project->tags->isNotEmpty())
                                <div class="project-details-info">Tags: <span>{{ $project->tags->pluck('name')->implode(', ') }}</span></div>
                            @endif
                            @if ($project->external_url)
                                <div class="project-details-info">Link: <span><a href="{{ $project->external_url }}" target="_blank" rel="noopener nofollow">Visit project</a></span></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
