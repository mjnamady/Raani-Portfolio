@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/admin-forms.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/latest-services.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $defaultImage = \App\Models\LatestService::DEFAULT_IMAGE;
    $isLive = (bool) old('is_active', $section->is_active);

    // The real upload limit: the app's own, lowered by PHP's upload_max_filesize when smaller.
    $maxImageBytes = \App\Support\UploadLimit::bytes(\App\Http\Requests\Admin\UpdateLatestServicesAreaRequest::MAX_IMAGE_KILOBYTES);
    $maxImageLabel = \App\Support\UploadLimit::label(\App\Http\Requests\Admin\UpdateLatestServicesAreaRequest::MAX_IMAGE_KILOBYTES);
@endphp

<div class="middle-content container-xxl p-0">

    <form method="POST" action="{{ route('admin.latest-services.update') }}" enctype="multipart/form-data" novalidate id="latest-services-form" data-track-changes>
        @csrf
        @method('PATCH')

        <!--  BEGIN BREADCRUMBS  -->
        <div class="secondary-nav">
            <div class="breadcrumbs-container" data-page-heading="Latest Service Area">
                <header class="header navbar navbar-expand-sm">
                    <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </a>
                    <div class="d-flex breadcrumb-content">
                        <div class="page-header">

                            <div class="page-title">
                            </div>

                            <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.banner') }}">Home Page</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Latest Service Area</li>
                                </ol>
                            </nav>

                        </div>
                    </div>
                    <ul class="navbar-nav flex-row ms-auto align-items-center gap-2">
                        <li class="nav-item">
                            <div class="ls-visibility-pill {{ $isLive ? 'is-live' : '' }}" id="ls-visibility-pill">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked($isLive)>
                                    <label class="form-check-label" for="is_active">
                                        <span class="ls-dot" aria-hidden="true"></span>
                                        <span class="ls-visibility-text">{{ $isLive ? 'Live on home page' : 'Hidden' }}</span>
                                    </label>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-external-link" style="width:16px;height:16px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                                View on site
                            </a>
                        </li>
                    </ul>
                </header>
            </div>
        </div>
        <!--  END BREADCRUMBS  -->

        <div class="layout-top-spacing">

            <!-- ===================== HEADING: edited in place, styled like the public section ===================== -->
            <div class="ls-hero">
                <span class="ls-hero-tag">Section heading</span>

                <div class="ls-hero-field ls-hero-eyebrow">
                    <label for="eyebrow" class="visually-hidden">Eyebrow</label>
                    <input type="text" class="form-control @error('eyebrow') is-invalid @enderror" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $section->eyebrow) }}" maxlength="60" placeholder="Eyebrow (optional)" data-char-count>
                    <span class="char-count" data-for="eyebrow"></span>
                    @error('eyebrow')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="ls-hero-field ls-hero-title">
                    <label for="title" class="visually-hidden">Title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $section->title) }}" required maxlength="120" placeholder="Section title" data-char-count>
                    <span class="char-count" data-for="title"></span>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="ls-hero-field ls-hero-description">
                    <label for="subtitle" class="visually-hidden">Description</label>
                    <textarea class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" rows="2" maxlength="255" placeholder="Short description (optional)" data-char-count data-autosize>{{ old('subtitle', $section->subtitle) }}</textarea>
                    <span class="char-count" data-for="subtitle"></span>
                    @error('subtitle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <p class="ls-hero-hint">Click any text to edit it. Empty eyebrow or description are hidden on the site.</p>
            </div>

            <div class="row g-4">

                <!-- ===================== LEFT: NUMBERED ITEMS ===================== -->
                <div class="col-xl-7 col-lg-12">
                    <div class="ls-panel-head">
                        <h5>Services</h5>
                        <span class="text-muted">Up to 3. Numbered in this order; empty cards are skipped.</span>
                    </div>

                    @error('items')
                        <div class="alert alert-light-danger mb-3">{{ $message }}</div>
                    @enderror

                    @foreach ($itemRows as $i => $row)
                        <div class="ls-item">
                            <div class="ls-item-number" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}.</div>
                            <div class="ls-item-body">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="ls-item-label">Item {{ $i + 1 }}</span>
                                    <span class="ls-item-status"></span>
                                </div>

                                <label for="items_{{ $i }}_title" class="visually-hidden">Item {{ $i + 1 }} title</label>
                                <input type="text" class="form-control ls-item-title @error("items.$i.title") is-invalid @enderror" id="items_{{ $i }}_title" name="items[{{ $i }}][title]" value="{{ old("items.$i.title", $row?->title) }}" maxlength="120" placeholder="Title, e.g. A Portfolio of Creativity" data-item="title">
                                @error("items.$i.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="items_{{ $i }}_description" class="visually-hidden">Item {{ $i + 1 }} description</label>
                                <textarea class="form-control ls-item-description @error("items.$i.description") is-invalid @enderror" id="items_{{ $i }}_description" name="items[{{ $i }}][description]" rows="2" maxlength="300" placeholder="A sentence or two about this service" data-char-count data-autosize data-item="description">{{ old("items.$i.description", $row?->description) }}</textarea>
                                <div class="d-flex justify-content-end">
                                    <span class="char-count" data-for="items_{{ $i }}_description"></span>
                                </div>
                                @error("items.$i.description")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ===================== RIGHT: SIDE IMAGE DROP ZONE ===================== -->
                <div class="col-xl-5 col-lg-12">
                    <div class="ls-panel-head">
                        <h5>Side image</h5>
                        <span class="text-muted">Shown next to the list.</span>
                    </div>

                    <div class="ls-image-card">
                        <input type="file" class="visually-hidden" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                               data-max-bytes="{{ $maxImageBytes }}"
                               data-max-label="{{ $maxImageLabel }}">
                        <label for="image" class="ls-dropzone @error('image') is-invalid @enderror" id="ls-dropzone">
                            <img id="latest-image-preview"
                                 src="{{ $section->imageUrl($defaultImage) }}"
                                 data-original="{{ $section->imageUrl($defaultImage) }}"
                                 alt="Side image preview">
                            <span class="ls-dropzone-overlay">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path><polyline points="7 9 12 4 17 9"></polyline><line x1="12" y1="4" x2="12" y2="16"></line></svg>
                                <strong>Drop an image here</strong>
                                <span>or click to browse</span>
                            </span>
                            <span class="badge badge-primary ls-new-badge">New</span>
                        </label>

                        <div id="latest-image-client-error" class="invalid-feedback d-none"></div>
                        @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">JPG, PNG or WebP · max {{ $maxImageLabel }} · cropped to {{ \App\Models\LatestService::IMAGE_WIDTH }}&times;{{ \App\Models\LatestService::IMAGE_HEIGHT }}</small>
                            <button type="button" id="latest-image-reset" class="btn btn-light-dark btn-sm d-none">Undo</button>
                        </div>

                        <div class="mt-3">
                            <label for="image_alt">Image description (alt text)</label>
                            <input type="text" class="form-control @error('image_alt') is-invalid @enderror" id="image_alt" name="image_alt" value="{{ old('image_alt', $section->image_alt) }}" required maxlength="150">
                            @error('image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Read by screen readers and search engines.</div>
                        </div>
                    </div>
                </div>

            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save latest service area'])

        </div>
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/latest-services.js') }}"></script>
@endpush
