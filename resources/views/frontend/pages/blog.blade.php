@extends('frontend.pages.layout.pages_temp')
@section('title', 'Blog')
@section('d-content')
    <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">Blog</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            @if ($activeCategory)
                                <li class="tmp-breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                                <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                                <li class="tmp-breadcrumb-item active">{{ $activeCategory->name }}</li>
                            @else
                                <li class="tmp-breadcrumb-item active">Blog</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->

    <div class="blog-classic-area-wrapper tmp-section-gap">
        <div class="container">
            {{-- Category filter links: real links, so filtering works with pagination and without JavaScript (V-3). --}}
            @if ($categories->isNotEmpty())
                <nav class="blog-category-filter" aria-label="Filter posts by category">
                    <a href="{{ route('blog.index') }}" class="{{ $activeCategory ? '' : 'active' }}" @unless ($activeCategory) aria-current="page" @endunless>All</a>
                    @foreach ($categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="{{ $activeCategory?->is($category) ? 'active' : '' }}" @if ($activeCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($posts->isEmpty())
                <p class="text-center py-5 mb-0">No posts to show yet. Please check back soon.</p>
            @else
                <div class="row">
                    @foreach ($posts as $post)
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <div class="blog-card tmp-hover-link image-box-hover tmp-scroll-trigger tmp-fade-in animation-order-{{ ($loop->index % 3) + 1 }}">
                                <div class="img-box">
                                    <a href="{{ route('blog.show', $post->slug) }}">
                                        @if ($post->card_image_url)
                                            <img class="w-100" src="{{ $post->card_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" width="{{ \App\Models\Post::CARD_WIDTH }}" height="{{ \App\Models\Post::CARD_HEIGHT }}" loading="lazy">
                                        @endif
                                    </a>
                                    <ul class="blog-tags">
                                        <li><span class="tag-icon"><i class="fa-regular fa-user"></i></span>{{ $post->author_name }}</li>
                                        <li><span class="tag-icon"><i class="fa-solid fa-calendar-days"></i></span><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j') }}</time></li>
                                    </ul>
                                </div>
                                <div class="blog-content-wrap">
                                    <h3 class="blog-title"><a class="link" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                                    <div class="more-btn tmp-link-animation">
                                        <a href="{{ route('blog.show', $post->slug) }}" class="read-more-btn" aria-label="Read more: {{ $post->title }}">Read More <span class="read-more-icon"><i class="fa-solid fa-angle-right"></i></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt--50">
                    {{ $posts->links('frontend.partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
