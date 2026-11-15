@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/blog.css') }}" rel="stylesheet" type="text/css" />
    @vite('resources/css/admin-editor.css')
@endpush

@section('content')

@php
    $isNew = ! $post->exists;
    $maxKilobytes = \App\Http\Requests\Admin\StorePostRequest::MAX_IMAGE_KILOBYTES;
    $maxBytes = \App\Support\UploadLimit::bytes($maxKilobytes);
    $maxLabel = \App\Support\UploadLimit::label($maxKilobytes);

    $oldTags = old('tags');
    $tagsValue = is_array($oldTags) ? implode(', ', array_filter($oldTags, 'is_string')) : ($oldTags ?? $post->tags->pluck('name')->implode(', '));

    $status = old('status', $post->status);
    $publishedAt = old('published_at', $post->published_at?->format('Y-m-d\TH:i'));
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.posts.partials.page_header', [
        'trail' => [['Posts', route('admin.posts.index')], [$isNew ? 'New post' : 'Edit post', null]],
        'actions' => array_values(array_filter([
            ['url' => route('admin.posts.index'), 'label' => '← All posts', 'style' => 'light'],
            ! $isNew && $post->isPublished() && Route::has('blog.show')
                ? ['url' => route('blog.show', $post->slug), 'label' => 'View on site', 'style' => 'light', 'external' => true]
                : null,
        ])),
    ])

    <form method="POST"
          action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}"
          enctype="multipart/form-data" novalidate data-track-changes class="layout-top-spacing" id="post-form">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== WRITING COLUMN ===================== -->
            <div class="col-xl-8">
                <div class="bl-paper">
                    <label for="title" class="visually-hidden">Title</label>
                    <input type="text" class="bl-paper-title @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title) }}" required maxlength="160" placeholder="Post title">
                    @error('title')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="bl-slug">
                        <label for="slug">URL</label>
                        <span class="bl-slug-prefix">/blog/</span>
                        <input type="text" class="@error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" maxlength="180" placeholder="made-from-the-title" data-slug-auto="{{ $isNew && blank(old('slug')) ? '1' : '0' }}">
                    </div>
                    @error('slug')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="bl-excerpt">
                        <div class="d-flex justify-content-between">
                            <label for="excerpt">Excerpt <span class="text-muted">(optional)</span></label>
                            <span class="char-count" data-for="excerpt"></span>
                        </div>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="2" maxlength="300" placeholder="One or two sentences that sum the post up." data-char-count>{{ old('excerpt', $post->excerpt) }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="bl-body">
                        <div class="d-flex justify-content-between align-items-end">
                            <label for="body">Post</label>
                            <small class="text-muted" data-rich-count-for="body"></small>
                        </div>
                        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="14" placeholder="Write your post…" data-rich-text>{{ old('body', $post->body) }}</textarea>
                        @error('body')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Headings, bold/italic, lists, quotes and links. Quotes show in the theme's red quote box. Anything else (scripts, styles, images) is removed when you save.</div>
                    </div>
                </div>
            </div>

            <!-- ===================== SIDE PANEL ===================== -->
            <div class="col-xl-4">
                <div class="bl-side">

                    <!-- Publish -->
                    <div class="bl-card">
                        <h6>Publish</h6>
                        <div class="bl-status-switch" role="radiogroup" aria-label="Status">
                            <input type="radio" class="visually-hidden" name="status" id="status_draft" value="draft" @checked($status !== 'published')>
                            <label for="status_draft">Draft</label>
                            <input type="radio" class="visually-hidden" name="status" id="status_published" value="published" @checked($status === 'published')>
                            <label for="status_published">Published</label>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="mt-3">
                            <label for="published_at">Publish date <span class="text-muted">(optional)</span></label>
                            <input type="datetime-local" class="form-control form-control-sm @error('published_at') is-invalid @enderror" id="published_at" name="published_at" value="{{ $publishedAt }}" max="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Left empty, it is set when you first publish. It can be in the past, not the future.</div>
                        </div>

                        <div class="mt-3 mb-0">
                            <label for="author_name">Author</label>
                            <input type="text" class="form-control form-control-sm @error('author_name') is-invalid @enderror" id="author_name" name="author_name" value="{{ old('author_name', $post->author_name) }}" required maxlength="80">
                            @error('author_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mt-3 mb-0">
                            <input type="hidden" name="allow_comments" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="allow_comments" name="allow_comments" value="1" @checked(old('allow_comments', $post->allow_comments ?? true))>
                            <label class="form-check-label" for="allow_comments">Allow comments</label>
                        </div>
                        @if (! $isNew)
                            <p class="bl-live-note mb-0 mt-2"><a href="{{ route('admin.comments.index', ['post' => $post->id]) }}">Moderate this post's comments</a></p>
                        @endif
                    </div>

                    <!-- Category + tags -->
                    <div class="bl-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Category</h6>
                            <a href="{{ route('admin.post-categories.index') }}" class="small">Manage</a>
                        </div>
                        <label for="category_id" class="visually-hidden">Category</label>
                        <select class="form-select form-select-sm @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">Uncategorised</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <h6 class="mt-3">Tags</h6>
                        <label for="tags" class="visually-hidden">Tags</label>
                        <input type="text" class="form-control form-control-sm @error('tags') is-invalid @enderror @error('tags.*') is-invalid @enderror" id="tags" name="tags" value="{{ $tagsValue }}" placeholder="UX Design, Graphics" list="bl-tag-suggestions" autocomplete="off">
                        <datalist id="bl-tag-suggestions">
                            @foreach ($tagSuggestions as $suggestion)
                                <option value="{{ $suggestion }}"></option>
                            @endforeach
                        </datalist>
                        @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('tags.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="bl-tag-chips" id="bl-tag-chips" aria-hidden="true"></div>
                        <div class="form-text">Separate tags with commas (up to {{ \App\Http\Requests\Admin\StorePostRequest::MAX_TAGS }}). New tags are created automatically.</div>
                    </div>

                    <!-- Featured image -->
                    <div class="bl-card">
                        <h6>Featured image</h6>
                        <input type="file" class="visually-hidden" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/webp" data-image-input data-max-bytes="{{ $maxBytes }}" data-max-label="{{ $maxLabel }}">
                        <label for="featured_image" class="bl-drop @error('featured_image') is-invalid @enderror">
                            <img src="{{ $post->featured_image_url }}" data-original="{{ $post->featured_image_url }}" alt="Featured image preview" @unless ($post->featured_image_url) hidden @endunless>
                            <span class="bl-drop-empty" @if ($post->featured_image_url) hidden @endif>Click to choose the featured image</span>
                        </label>
                        <div class="bl-image-error invalid-feedback"></div>
                        @error('featured_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control form-control-sm mt-2 @error('featured_image_alt') is-invalid @enderror" name="featured_image_alt" value="{{ old('featured_image_alt', $post->featured_image_alt) }}" maxlength="160" placeholder="Image description (alt text)" aria-label="Featured image description">
                        <div class="form-text">Required to publish. Cropped to {{ \App\Models\Post::FEATURED_WIDTH }}&times;{{ \App\Models\Post::FEATURED_HEIGHT }} for the post page and {{ \App\Models\Post::CARD_WIDTH }}&times;{{ \App\Models\Post::CARD_HEIGHT }} for the cards · max {{ $maxLabel }}</div>
                    </div>

                    <!-- SEO -->
                    <div class="bl-card">
                        <h6>Search engines <span class="text-muted fw-normal">(optional)</span></h6>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <label for="meta_title">SEO title</label>
                                <span class="char-count" data-for="meta_title"></span>
                            </div>
                            <input type="text" class="form-control form-control-sm @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="70" placeholder="Defaults to the post title" data-char-count>
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <div class="d-flex justify-content-between">
                                <label for="meta_description">SEO description</label>
                                <span class="char-count" data-for="meta_description"></span>
                            </div>
                            <textarea class="form-control form-control-sm @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3" maxlength="160" placeholder="Defaults to the excerpt" data-char-count>{{ old('meta_description', $post->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Save post' : 'Save changes'])
    </form>

</div>

@endsection

@push('scripts')
    @vite('resources/js/admin-editor.js')
    <script src="{{ asset('backend/custom/js/blog-editor.js') }}"></script>
@endpush
