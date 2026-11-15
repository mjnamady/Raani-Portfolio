@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/projects.css') }}" rel="stylesheet" type="text/css" />
    @vite('resources/css/admin-editor.css')
@endpush

@section('content')

@php
    $isNew = ! $project->exists;
    $maxKilobytes = \App\Http\Requests\Admin\StoreProjectRequest::MAX_IMAGE_KILOBYTES;
    $maxBytes = \App\Support\UploadLimit::bytes($maxKilobytes);
    $maxLabel = \App\Support\UploadLimit::label($maxKilobytes);

    $oldTags = old('tags');
    $tagsValue = is_array($oldTags) ? implode(', ', $oldTags) : ($oldTags ?? $project->tags->pluck('name')->implode(', '));

    $oldFeatures = old('features');
    $features = is_array($oldFeatures) ? $oldFeatures : ($project->features ?? []);

    $status = old('status', $project->status);
    $publishedAt = old('published_at', $project->published_at?->format('Y-m-d\TH:i'));
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.projects.partials.page_header', [
        'trail' => [['Projects', route('admin.projects.index')], [$isNew ? 'New project' : 'Edit project', null]],
        'actions' => [['url' => route('admin.projects.index'), 'label' => '← All projects', 'style' => 'light']],
    ])

    <form method="POST"
          action="{{ $isNew ? route('admin.projects.store') : route('admin.projects.update', $project) }}"
          enctype="multipart/form-data" novalidate data-track-changes class="layout-top-spacing" id="project-form">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== MAIN COLUMN: THE DOCUMENT ===================== -->
            <div class="col-xl-8">
                <div class="pj-doc">
                    <label for="title" class="visually-hidden">Title</label>
                    <input type="text" class="pj-doc-title @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $project->title) }}" required maxlength="120" placeholder="Project title">
                    @error('title')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="pj-slug">
                        <label for="slug">URL</label>
                        <span class="pj-slug-prefix">/projects/</span>
                        <input type="text" class="@error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $project->slug) }}" maxlength="140" placeholder="made-from-the-title" data-slug-auto="{{ $isNew && blank(old('slug')) ? '1' : '0' }}">
                    </div>
                    @error('slug')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="mt-3">
                        <label for="tagline">Card tagline <span class="text-muted">(optional)</span></label>
                        <input type="text" class="form-control @error('tagline') is-invalid @enderror" id="tagline" name="tagline" value="{{ old('tagline', $project->tagline) }}" maxlength="80" placeholder="e.g. Development Coaches">
                        @error('tagline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">The small line under the title on the home page cards.</div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-end">
                            <label for="content">Content</label>
                            <small class="text-muted" data-rich-count-for="content"></small>
                        </div>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" placeholder="Describe the project: the brief, your approach and the result…" data-rich-text>{{ old('content', $project->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Headings, bold/italic, lists, quotes and links. Anything else (scripts, styles, images) is removed when you save.</div>
                    </div>
                </div>

                <!-- Features checklist -->
                <div class="pj-panel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0">Key features</h6>
                            <small class="text-muted">The checklist on the project page (up to {{ \App\Http\Requests\Admin\StoreProjectRequest::MAX_FEATURES }}).</small>
                        </div>
                        <button type="button" class="btn btn-light-primary btn-sm" id="pj-add-feature">+ Add feature</button>
                    </div>

                    @error('features')
                        <div class="alert alert-light-danger py-2 mb-2">{{ $message }}</div>
                    @enderror

                    <ol class="pj-features" id="pj-features" data-max="{{ \App\Http\Requests\Admin\StoreProjectRequest::MAX_FEATURES }}">
                        @foreach ($features as $i => $feature)
                            <li class="pj-feature">
                                <span class="pj-feature-check" aria-hidden="true">✓</span>
                                <input type="text" class="form-control form-control-sm @error("features.$i") is-invalid @enderror" name="features[]" value="{{ $feature }}" maxlength="80" aria-label="Feature {{ $loop->iteration }}">
                                <button type="button" class="pj-icon-btn" data-remove-feature aria-label="Remove feature">✕</button>
                            </li>
                        @endforeach
                    </ol>
                    <p class="pj-features-empty text-muted mb-0 {{ count($features) ? 'd-none' : '' }}" id="pj-features-empty">No features yet.</p>

                    <template id="pj-feature-template">
                        <li class="pj-feature">
                            <span class="pj-feature-check" aria-hidden="true">✓</span>
                            <input type="text" class="form-control form-control-sm" name="features[]" value="" maxlength="80" aria-label="Feature">
                            <button type="button" class="pj-icon-btn" data-remove-feature aria-label="Remove feature">✕</button>
                        </li>
                    </template>
                </div>
            </div>

            <!-- ===================== SIDEBAR ===================== -->
            <div class="col-xl-4">
                <div class="pj-sidebar">

                    <!-- Publish -->
                    <div class="pj-box">
                        <h6>Publish</h6>
                        <div class="pj-status-switch" role="radiogroup" aria-label="Status">
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
                            <input type="datetime-local" class="form-control form-control-sm @error('published_at') is-invalid @enderror" id="published_at" name="published_at" value="{{ $publishedAt }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Left empty, it is set when you first publish. A future date keeps the project hidden until then.</div>
                        </div>

                        <div class="form-check form-switch mt-3 mb-0">
                            <input type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $project->is_featured))>
                            <label class="form-check-label" for="is_featured">★ Feature on the home page</label>
                        </div>

                        @if (! $isNew && $project->isPublished())
                            <p class="pj-live-link mb-0 mt-3">Live at <a href="{{ route('projects.show', $project->slug) }}" target="_blank" rel="noopener"><code>/projects/{{ $project->slug }}</code></a></p>
                        @endif
                    </div>

                    <!-- Category + tags -->
                    <div class="pj-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Category</h6>
                            <a href="{{ route('admin.project-categories.index') }}" class="small">Manage</a>
                        </div>
                        <label for="category_id" class="visually-hidden">Category</label>
                        <select class="form-select form-select-sm @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">Uncategorised</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $project->category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <h6 class="mt-3">Tags</h6>
                        <label for="tags" class="visually-hidden">Tags</label>
                        <input type="text" class="form-control form-control-sm @error('tags') is-invalid @enderror @error('tags.*') is-invalid @enderror" id="tags" name="tags" value="{{ $tagsValue }}" placeholder="Figma, Webflow, Branding" list="pj-tag-suggestions" autocomplete="off">
                        <datalist id="pj-tag-suggestions">
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
                        <div class="pj-tag-chips" id="pj-tag-chips" aria-hidden="true"></div>
                        <div class="form-text">Separate tags with commas. New tags are created automatically.</div>
                    </div>

                    <!-- Images -->
                    <div class="pj-box">
                        <h6>Card image</h6>
                        <input type="file" class="visually-hidden" id="card_image" name="card_image" accept="image/jpeg,image/png,image/webp" data-image-input data-max-bytes="{{ $maxBytes }}" data-max-label="{{ $maxLabel }}">
                        <label for="card_image" class="pj-drop pj-drop-card @error('card_image') is-invalid @enderror">
                            <img src="{{ $project->card_image_url }}" data-original="{{ $project->card_image_url }}" alt="Card image preview" @unless ($project->card_image_url) hidden @endunless>
                            <span class="pj-drop-empty" @if ($project->card_image_url) hidden @endif>Click to choose the card image</span>
                        </label>
                        <div class="pj-image-error invalid-feedback"></div>
                        @error('card_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control form-control-sm mt-2 @error('card_image_alt') is-invalid @enderror" name="card_image_alt" value="{{ old('card_image_alt', $project->card_image_alt) }}" maxlength="150" placeholder="Image description (alt text)" aria-label="Card image description">
                        <div class="form-text">Home and projects page cards · cropped to {{ \App\Models\Project::CARD_WIDTH }}&times;{{ \App\Models\Project::CARD_HEIGHT }} · max {{ $maxLabel }}</div>

                        <h6 class="mt-4">Cover image</h6>
                        <input type="file" class="visually-hidden" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp" data-image-input data-max-bytes="{{ $maxBytes }}" data-max-label="{{ $maxLabel }}">
                        <label for="cover_image" class="pj-drop pj-drop-cover @error('cover_image') is-invalid @enderror">
                            <img src="{{ $project->cover_image_url }}" data-original="{{ $project->cover_image_url }}" alt="Cover image preview" @unless ($project->cover_image_url) hidden @endunless>
                            <span class="pj-drop-empty" @if ($project->cover_image_url) hidden @endif>Click to choose the cover image</span>
                        </label>
                        <div class="pj-image-error invalid-feedback"></div>
                        @error('cover_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control form-control-sm mt-2 @error('cover_image_alt') is-invalid @enderror" name="cover_image_alt" value="{{ old('cover_image_alt', $project->cover_image_alt) }}" maxlength="150" placeholder="Image description (alt text)" aria-label="Cover image description">
                        <div class="form-text">Top of the project page · cropped to {{ \App\Models\Project::COVER_WIDTH }}&times;{{ \App\Models\Project::COVER_HEIGHT }} · max {{ $maxLabel }}</div>
                    </div>

                    <!-- Details -->
                    <div class="pj-box">
                        <h6>Project details</h6>
                        <div class="mb-2">
                            <label for="client_name">Client / author</label>
                            <input type="text" class="form-control form-control-sm @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $project->client_name) }}" maxlength="80">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="project_date">Project date</label>
                            <input type="date" class="form-control form-control-sm @error('project_date') is-invalid @enderror" id="project_date" name="project_date" value="{{ old('project_date', $project->project_date?->format('Y-m-d')) }}">
                            @error('project_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="external_url">Project link <span class="text-muted">(optional)</span></label>
                            <input type="url" class="form-control form-control-sm @error('external_url') is-invalid @enderror" id="external_url" name="external_url" value="{{ old('external_url', $project->external_url) }}" maxlength="255" placeholder="https://example.com" inputmode="url">
                            @error('external_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Create project' : 'Save project'])
    </form>

    @unless ($isNew)
        @include('admin.projects.partials.gallery')
    @else
        <div class="pj-gallery-later">The gallery becomes available once the project is created.</div>
    @endunless

</div>

@endsection

@push('scripts')
    @vite('resources/js/admin-editor.js')
    <script src="{{ asset('backend/custom/js/project-editor.js') }}"></script>
@endpush
