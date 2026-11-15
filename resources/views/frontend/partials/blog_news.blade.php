{{-- "Blog and News" section (HOME-10): the latest published posts. --}}
@if ($blogVisible && $homePosts->isNotEmpty())
@php
    // Theme defaults apply only until the heading is saved; an emptied small heading stays hidden.
    $eyebrow = $blogSection ? $blogSection->eyebrow : 'Blog and news';
    $titleLines = preg_split('/\R/', (string) ($blogSection ? $blogSection->title : "Elevating Personal Branding the\nthrough Powerful Portfolios"));
@endphp
<section class="blog-and-news-are tmp-section-gap">
    <div class="container">
        <div class="section-head mb--40">
            @if (filled($eyebrow))
                <div class="section-sub-title center-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                    <span class="subtitle">{{ $eyebrow }}</span>
                </div>
            @endif
            <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">@foreach ($titleLines as $line){{ trim($line) }}@unless ($loop->last) <br> @endunless @endforeach</h2>
        </div>
        <div class="row">
            @foreach ($homePosts as $post)
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="blog-card tmp-hover-link tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                        <div class="img-box">
                            <a href="{{ route('blog.show', $post->slug) }}">
                                @if ($post->card_image_url)
                                    <img class="img-primary hidden-on-mobile" src="{{ $post->card_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" width="{{ \App\Models\Post::CARD_WIDTH }}" height="{{ \App\Models\Post::CARD_HEIGHT }}" loading="lazy">
                                    <img class="img-secondary" src="{{ $post->card_image_url }}" alt="" width="{{ \App\Models\Post::CARD_WIDTH }}" height="{{ \App\Models\Post::CARD_HEIGHT }}" loading="lazy">
                                @endif
                            </a>
                            <ul class="blog-tags">
                                <li><span class="tag-icon"><i class="fa-regular fa-user"></i></span>{{ $post->author_name }}</li>
                                <li><span class="tag-icon"><i class="fa-solid fa-calendar-days"></i></span><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j') }}</time></li>
                            </ul>
                        </div>
                        <div class="blog-content-wrap">
                            <h3 class="blog-title v2"><a class="link" href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                            <a href="{{ route('blog.show', $post->slug) }}" class="read-more-btn v2" aria-label="Read more: {{ $post->title }}">Read More <span class="read-more-icon"><i class="fa-solid fa-angle-right"></i></span></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
