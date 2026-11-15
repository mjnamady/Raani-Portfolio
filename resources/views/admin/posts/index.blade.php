@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/blog.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.posts.partials.page_header', [
        'trail' => [['Posts', null]],
        'actions' => [
            ['url' => route('admin.post-categories.index'), 'label' => 'Categories', 'style' => 'light'],
            ['url' => route('admin.posts.create'), 'label' => '+ New post'],
        ],
    ])

    <div class="row g-4 layout-top-spacing">

        <!-- ===================== THE DESK: POSTS ===================== -->
        <div class="col-xl-8">
            <div class="bl-desk">
                <div class="bl-desk-head">
                    <nav class="bl-tabs" aria-label="Filter by status">
                        <a href="{{ route('admin.posts.index') }}" class="{{ $status === null ? 'is-active' : '' }}" @if ($status === null) aria-current="page" @endif>All <span>{{ $counts['all'] }}</span></a>
                        <a href="{{ route('admin.posts.index', ['status' => 'published']) }}" class="{{ $status === 'published' ? 'is-active' : '' }}" @if ($status === 'published') aria-current="page" @endif>Published <span>{{ $counts['published'] }}</span></a>
                        <a href="{{ route('admin.posts.index', ['status' => 'draft']) }}" class="{{ $status === 'draft' ? 'is-active' : '' }}" @if ($status === 'draft') aria-current="page" @endif>Drafts <span>{{ $counts['draft'] }}</span></a>
                    </nav>
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">+ New post</a>
                </div>

                <a href="{{ route('admin.posts.create') }}" class="bl-new-row">
                    <span class="bl-new-plus" aria-hidden="true">+</span>
                    <span>
                        <strong>{{ $counts['all'] === 0 ? 'Write your first post' : 'Write a new post' }}</strong>
                        <small>Start as a draft; publish when the text and featured image are ready.</small>
                    </span>
                </a>

                @forelse ($posts as $post)
                    <article class="bl-row">
                        <div class="bl-thumb">
                            @if ($post->card_image_url)
                                <img src="{{ $post->card_image_url }}" alt="" loading="lazy">
                            @else
                                <span aria-hidden="true">No image</span>
                            @endif
                        </div>

                        <div class="bl-main">
                            <div class="bl-meta">
                                <span class="bl-status {{ $post->isPublished() ? 'is-published' : 'is-draft' }}">{{ $post->isPublished() ? 'Published' : 'Draft' }}</span>
                                <span>{{ $post->category?->name ?? 'Uncategorised' }}</span>
                                <span>·</span>
                                <span>{{ $post->published_at ? $post->published_at->format('j M Y') : 'Not published yet' }}</span>
                            </div>
                            <h5><a href="{{ route('admin.posts.edit', $post) }}">{{ $post->title }}</a></h5>
                            <p class="bl-byline">
                                By {{ $post->author_name }}
                                @if ($post->comments_count)
                                    · <a href="{{ route('admin.comments.index', ['post' => $post->id]) }}">{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}@if ($post->pending_comments_count) ({{ $post->pending_comments_count }} pending)@endif</a>
                                @endif
                                @unless ($post->allow_comments)
                                    · comments closed
                                @endunless
                            </p>
                            @if ($post->tags->isNotEmpty())
                                <div class="bl-tags">
                                    @foreach ($post->tags as $tag)
                                        <span>{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="bl-actions">
                            <form method="POST" action="{{ route('admin.posts.publish', $post) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $post->isPublished() ? 'btn-light-dark' : 'btn-light-success' }}">{{ $post->isPublished() ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="bl-icon-btn" title="Edit" aria-label="Edit {{ $post->title }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                            </a>
                            @if ($post->isPublished() && Route::has('blog.show'))
                                <a href="{{ route('blog.show', $post->slug) }}" class="bl-icon-btn" target="_blank" rel="noopener" title="View on site" aria-label="View {{ $post->title }} on the site">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6"/><path d="M11 13l9 -9"/><path d="M15 4h5v5"/></svg>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Delete the post &quot;{{ $post->title }}&quot;? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bl-icon-btn bl-delete" title="Delete" aria-label="Delete {{ $post->title }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="bl-empty">
                        @if ($status === 'draft')
                            No drafts.
                        @elseif ($status === 'published')
                            Nothing is published yet. The Blog and News section stays hidden on the site until a post is published.
                        @else
                            No posts yet. The Blog and News section stays hidden on the site until a post is published.
                        @endif
                    </p>
                @endforelse

                @if ($posts->hasPages())
                    <div class="bl-pagination">
                        {{ $posts->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ===================== HOME SECTION SETTINGS ===================== -->
        <div class="col-xl-4">
            <form method="POST" action="{{ route('admin.posts.settings') }}" novalidate data-track-changes class="bl-section">
                @csrf
                @method('PUT')

                <div class="bl-card">
                    <h5>Home page "Blog and News"</h5>
                    <p class="bl-subtitle">Shows the {{ \App\Models\Post::HOME_LIMIT }} latest published posts under this heading.</p>

                    <div class="mb-3">
                        <label for="eyebrow">Small heading</label>
                        <input type="text" class="form-control @error('eyebrow') is-invalid @enderror" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $section->eyebrow) }}" maxlength="60">
                        @error('eyebrow')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title">Title</label>
                        <textarea class="form-control @error('title') is-invalid @enderror" id="title" name="title" rows="2" required maxlength="120">{{ old('title', $section->title) }}</textarea>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Press Enter where the title should break onto a new line.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                        <label class="form-check-label" for="is_active">Show on the home page</label>
                    </div>
                </div>

                @include('admin.layout.components.save_bar', ['label' => 'Save section'])
            </form>
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/blog-editor.js') }}"></script>
@endpush
