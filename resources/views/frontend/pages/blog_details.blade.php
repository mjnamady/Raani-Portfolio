@extends('frontend.pages.layout.pages_temp')
@section('title', $post->title)
@section('d-content')
    <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">Blog Details</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item active">{{ Str::limit($post->title, 40) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->

    <div class="blog-classic-area-wrapper tmp-section-gap">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <article class="blog-details-left-area">
                        @if ($post->featured_image_url)
                            <div class="thumbnail-top">
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" width="{{ \App\Models\Post::FEATURED_WIDTH }}" height="{{ \App\Models\Post::FEATURED_HEIGHT }}" fetchpriority="high">
                            </div>
                        @endif
                        <div class="blog-details-discription">
                            <div class="blog-classic-tag">
                                <h4 class="title">By {{ $post->author_name }}</h4>
                                <ul>
                                    @if ($post->category)
                                        <li>
                                            <div class="tag-wrap">
                                                <i class="fa-solid fa-tag"></i>
                                                <h4 class="tag-title"><a href="{{ route('blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a></h4>
                                            </div>
                                        </li>
                                    @endif
                                    <li>
                                        <div class="tag-wrap">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            <h4 class="tag-title"><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time></h4>
                                        </div>
                                    </li>
                                    @if ($post->approved_comments_count || $post->allow_comments)
                                        <li>
                                            <div class="tag-wrap">
                                                <i class="fa-regular fa-comments"></i>
                                                <h4 class="tag-title"><a href="#comments">Comments ({{ str_pad((string) $post->approved_comments_count, 2, '0', STR_PAD_LEFT) }})</a></h4>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <h2 class="title blog-post-title">{{ $post->title }}</h2>

                            @if ($post->body)
                                {{-- Sanitized on save with the strict "rich_text" allow-list (App\Support\RichText). --}}
                                <div class="blog-rich-content">
                                    {!! $post->body !!}
                                </div>
                            @endif
                        </div>

                        <!-- Previous / next post (BLG-5) -->
                        @if ($previous || $next)
                            <nav class="our-portfolio-swiper-btn-wrap blog-post-pager" aria-label="More posts">
                                @if ($previous)
                                    <a href="{{ route('blog.show', $previous->slug) }}" class="prev-btn" rel="prev">
                                        <div class="tmp-arrow-btn">
                                            <i class="fa-solid fa-arrow-left"></i>
                                        </div>
                                        <div class="btn-content">
                                            <span class="para">Previous post</span>
                                            <h4 class="title">{{ Str::limit($previous->title, 40) }}</h4>
                                        </div>
                                    </a>
                                @endif
                                @if ($next)
                                    <a href="{{ route('blog.show', $next->slug) }}" class="next-btn" rel="next">
                                        <div class="btn-content">
                                            <span class="para">Next post</span>
                                            <h4 class="title">{{ Str::limit($next->title, 40) }}</h4>
                                        </div>
                                        <div class="tmp-arrow-btn">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </div>
                                    </a>
                                @endif
                            </nav>
                        @endif

                        @if ($post->tags->isNotEmpty())
                            <div class="blog-details-navigation mt--40">
                                <div class="navigation-tags">
                                    <h3 class="tag-title">Keyword:</h3>
                                    <ul>
                                        @foreach ($post->tags as $tag)
                                            <li>
                                                <p class="tag">{{ $tag->name }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- Comments (BLG-9): approved only, one level of replies; plain text, escaped -->
                        @if ($post->approvedComments->isNotEmpty())
                            <section class="comment-area-main-wrapper mt--50" id="comments" aria-labelledby="comments-title">
                                <h3 class="title" id="comments-title">Comments ({{ $post->approved_comments_count }})</h3>
                                @foreach ($post->approvedComments as $comment)
                                    @include('frontend.partials.blog_comment', ['comment' => $comment, 'isReply' => false])
                                    @foreach ($comment->replies as $reply)
                                        @include('frontend.partials.blog_comment', ['comment' => $reply, 'isReply' => true])
                                    @endforeach
                                @endforeach
                            </section>
                        @else
                            <span id="comments"></span>
                        @endif

                        @if ($post->allow_comments)
                            <!-- Blog Details Form Wrapper Start -->
                            <div class="blog-details-form-wrapper tmponhover" id="comment-form">
                                <h4 class="title">{{ $replyTo ? 'Reply to '.$replyTo->author_name : 'Leave a comment' }}</h4>
                                <span class="subtitle">Your email won't be published. Comments appear after approval.</span>

                                @if (session('comment_status'))
                                    <div class="comment-form-status" role="status">{{ session('comment_status') }}</div>
                                @endif
                                @error('parent_id', 'comment')
                                    <div class="comment-form-error" role="alert">{{ $message }}</div>
                                @enderror

                                <form action="{{ route('blog.comments.store', $post->slug) }}" method="POST" class="blog-details-form" novalidate>
                                    @csrf
                                    @if ($replyTo)
                                        <input type="hidden" name="parent_id" value="{{ $replyTo->id }}">
                                        <p class="comment-replying">Replying to {{ $replyTo->author_name }} · <a href="{{ route('blog.show', $post->slug) }}#comment-form">cancel</a></p>
                                    @endif

                                    {{-- Honeypot (CON-4): hidden from people and screen readers; bots tend to fill it in. --}}
                                    <div class="comment-hp" aria-hidden="true">
                                        <label for="comment-hp">Leave this field empty</label>
                                        <input type="text" id="comment-hp" name="{{ \App\Http\Requests\StoreCommentRequest::HONEYPOT }}" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore="true" data-bwignore="true" data-form-type="other">
                                    </div>

                                    <div class="single-input">
                                        <label for="comment-name">Your Name</label>
                                        <input type="text" id="comment-name" name="author_name" value="{{ old('author_name') }}" placeholder="Name" maxlength="80" required autocomplete="name" @error('author_name', 'comment') aria-invalid="true" aria-describedby="comment-name-error" @enderror>
                                        @error('author_name', 'comment')
                                            <span class="comment-field-error" id="comment-name-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="single-input">
                                        <label for="comment-email">Your Email</label>
                                        <input type="email" id="comment-email" name="author_email" value="{{ old('author_email') }}" placeholder="Email" maxlength="255" required autocomplete="email" @error('author_email', 'comment') aria-invalid="true" aria-describedby="comment-email-error" @enderror>
                                        @error('author_email', 'comment')
                                            <span class="comment-field-error" id="comment-email-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <label for="comment-body">Message</label>
                                    <textarea id="comment-body" name="body" placeholder="Message here.." maxlength="{{ \App\Models\Comment::MAX_BODY_LENGTH }}" required @error('body', 'comment') aria-invalid="true" aria-describedby="comment-body-error" @enderror>{{ old('body') }}</textarea>
                                    @error('body', 'comment')
                                        <span class="comment-field-error" id="comment-body-error">{{ $message }}</span>
                                    @enderror

                                    <div class="blog-submit-btn mt--40">
                                        <div class="tmp-button-here">
                                            <button type="submit" class="tmp-btn hover-icon-reverse radius-round w-100">
                                                <span class="icon-reverse-wrapper">
                                                    <span class="btn-text">{{ $replyTo ? 'Post Reply' : 'Submit Now' }}</span>
                                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                                    <span class="btn-icon"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Blog Details Form Wrapper End -->
                        @else
                            <p class="comments-closed" id="comment-form">Comments are closed on this post.</p>
                        @endif
                    </article>
                </div>
                <div class="col-lg-4">
                    <aside class="tmp-sidebar" aria-label="Blog sidebar">
                        @if ($sidebarCategories->isNotEmpty())
                            <div class="signle-side-bar recent-post-area tmponhover">
                                <div class="header">
                                    <h3 class="title">Categories</h3>
                                </div>
                                <div class="body">
                                    @foreach ($sidebarCategories as $category)
                                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="single-post">
                                            <span class="single-post-left">
                                                <i class="fa-solid fa-arrow-right"></i>
                                                <span class="post-title">{{ $category->name }}</span>
                                            </span>
                                            <span class="post-num">({{ str_pad((string) $category->posts_count, 2, '0', STR_PAD_LEFT) }})</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($recentPosts->isNotEmpty())
                            <div class="signle-side-bar recent-post-area tmponhover">
                                <div class="header">
                                    <h3 class="title">Recent Post</h3>
                                </div>
                                <div class="body">
                                    @foreach ($recentPosts as $recent)
                                        <div class="single-post-card tmp-hover-link">
                                            <div class="single-post-card-img">
                                                @if ($recent->card_image_url)
                                                    <img src="{{ $recent->card_image_url }}" alt="" width="80" height="80" loading="lazy">
                                                @endif
                                            </div>
                                            <div class="single-post-right">
                                                @if ($recent->category)
                                                    <div class="single-post-top">
                                                        <i class="fa-regular fa-folder-open"></i>
                                                        <p class="post-title">{{ $recent->category->name }}</p>
                                                    </div>
                                                @endif
                                                <h3 class="post-title"><a class="link" href="{{ route('blog.show', $recent->slug) }}">{{ $recent->title }}</a></h3>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($author)
                            <div class="signle-side-bar tmponhover">
                                <div class="header">
                                    <h3 class="title">About Me</h3>
                                </div>
                                <div class="body">
                                    <div class="about-me-details">
                                        <div class="about-me-details-head">
                                            <div class="about-me-img">
                                                <img src="{{ $author->image_url }}" alt="{{ $author->image_alt ?: $author->name }}" loading="lazy">
                                            </div>
                                            <div class="about-me-right-content">
                                                <h3 class="title">{{ $author->name }}</h3>
                                                @if (filled($author->role))
                                                    <p class="para">{{ Str::ucfirst(preg_replace('/^an?\s+/i', '', $author->role)) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if (filled($author->biography))
                                            <p class="about-me-para">{{ Str::limit($author->biography, 160) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($sidebarTags->isNotEmpty())
                            <div class="signle-side-bar tmponhover">
                                <div class="header">
                                    <h3 class="title">Tags</h3>
                                </div>
                                <div class="body">
                                    {{-- Plain labels: tag pages are deferred (MVP §2.2). --}}
                                    <div class="tags-wrapper">
                                        @foreach ($sidebarTags as $tag)
                                            <span class="tag-link">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection
