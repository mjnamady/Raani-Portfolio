@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/blog.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.posts.partials.page_header', [
        'trail' => [['Categories', null]],
        'actions' => [['url' => route('admin.posts.index'), 'label' => '← All posts', 'style' => 'light']],
    ])

    <div class="row g-4 layout-top-spacing">

        <!-- ===================== ADD ===================== -->
        <div class="col-xl-4">
            <form method="POST" action="{{ route('admin.post-categories.store') }}" novalidate data-track-changes class="bl-card">
                @csrf
                <h5>Add a category</h5>
                <p class="bl-subtitle">Categories appear on every post and in the blog sidebar, in the order below.</p>

                <div class="mb-3">
                    <label for="new-name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="new-name" name="name" value="{{ old('name') }}" required maxlength="60" placeholder="e.g. Web Design">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-0">
                    <label for="new-slug">URL slug <span class="text-muted">(optional)</span></label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="new-slug" name="slug" value="{{ old('slug') }}" maxlength="80" placeholder="made-from-the-name">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Used in filter links, e.g. <code>/blog?category=web-design</code>.</div>
                </div>

                @include('admin.layout.components.save_bar', ['label' => 'Add category'])
            </form>
        </div>

        <!-- ===================== LIST ===================== -->
        <div class="col-xl-8">
            <div class="bl-desk">
                @forelse ($categories as $category)
                    <div class="bl-cat-row">
                        <span class="bl-cat-position">{{ $loop->iteration }}</span>

                        <form method="POST" action="{{ route('admin.post-categories.update', $category) }}" class="bl-cat-edit">
                            @csrf
                            @method('PUT')
                            <label for="name-{{ $category->id }}" class="visually-hidden">Name</label>
                            <input type="text" class="form-control form-control-sm" id="name-{{ $category->id }}" name="name" value="{{ $category->name }}" required maxlength="60">
                            <label for="slug-{{ $category->id }}" class="visually-hidden">URL slug</label>
                            <input type="text" class="form-control form-control-sm bl-cat-slug" id="slug-{{ $category->id }}" name="slug" value="{{ $category->slug }}" maxlength="80">
                            <button type="submit" class="btn btn-light-primary btn-sm">Save</button>
                        </form>

                        <span class="bl-cat-count">{{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</span>

                        <div class="bl-cat-actions">
                            <form method="POST" action="{{ route('admin.post-categories.move', $category) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="bl-icon-btn" aria-label="Move {{ $category->name }} up" title="Move up" @disabled($loop->first)>↑</button>
                            </form>
                            <form method="POST" action="{{ route('admin.post-categories.move', $category) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="bl-icon-btn" aria-label="Move {{ $category->name }} down" title="Move down" @disabled($loop->last)>↓</button>
                            </form>
                            <form method="POST" action="{{ route('admin.post-categories.destroy', $category) }}"
                                  data-confirm="Delete the &quot;{{ $category->name }}&quot; category?{{ $category->posts_count ? ' Its '.$category->posts_count.' post(s) will become uncategorised.' : '' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bl-icon-btn bl-delete" aria-label="Delete {{ $category->name }}" title="Delete">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="bl-empty">No categories yet. Posts can stay uncategorised.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/blog-editor.js') }}"></script>
@endpush
