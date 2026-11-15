@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/resume-shared.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/resume-experience.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $defaultImage = \App\Models\ResumeEntry::DEFAULT_EXPERIENCE_IMAGE;
    $maxImageKilobytes = \App\Http\Requests\Admin\UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES;
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.resume.partials.page_header', [
        'current' => $config['label'],
        'action' => ['url' => route('admin.experience.create'), 'label' => '+ Add experience'],
    ])

    <div class="row g-4 layout-top-spacing">

        <!-- ===================== LEFT: SECTION SETTINGS + SIDE IMAGE ===================== -->
        <div class="col-xl-4 col-lg-5">
            <form method="POST" action="{{ route('admin.experience.settings') }}" enctype="multipart/form-data" novalidate data-track-changes class="ex-settings">
                @csrf
                @method('PUT')

                <div class="ex-panel">
                    <input type="file" class="visually-hidden" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                           data-max-bytes="{{ \App\Support\UploadLimit::bytes($maxImageKilobytes) }}"
                           data-max-label="{{ \App\Support\UploadLimit::label($maxImageKilobytes) }}">
                    <label for="image" class="ex-photo @error('image') is-invalid @enderror" id="ex-photo">
                        <img id="ex-photo-preview"
                             src="{{ $section->imageUrl($defaultImage) }}"
                             data-original="{{ $section->imageUrl($defaultImage) }}"
                             alt="Side image preview">
                        <span class="ex-photo-overlay">Change photo</span>
                        <span class="badge badge-primary ex-photo-badge">New</span>
                    </label>
                    <div id="ex-photo-error" class="invalid-feedback d-none"></div>
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">JPG, PNG or WebP · max {{ \App\Support\UploadLimit::label($maxImageKilobytes) }} · cropped to {{ \App\Models\ResumeEntry::IMAGE_WIDTH }}&times;{{ \App\Models\ResumeEntry::IMAGE_HEIGHT }}</small>
                        <button type="button" id="ex-photo-reset" class="btn btn-light-dark btn-sm d-none">Undo</button>
                    </div>

                    <div class="mt-3">
                        <label for="image_alt">Image description (alt text)</label>
                        <input type="text" class="form-control @error('image_alt') is-invalid @enderror" id="image_alt" name="image_alt" value="{{ old('image_alt', $section->image_alt) }}" required maxlength="150">
                        @error('image_alt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <label for="title">Section title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $section->title) }}" required maxlength="60">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mt-3 mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                        <label class="form-check-label" for="is_active">Show on the home page</label>
                    </div>
                </div>

                @include('admin.layout.components.save_bar', ['label' => 'Save section'])
            </form>
        </div>

        <!-- ===================== RIGHT: ENTRIES ===================== -->
        <div class="col-xl-8 col-lg-7">
            <div class="ex-list-head">
                <h4>Entries</h4>
                <span>{{ $entries->where('is_active', true)->count() }} of {{ $entries->count() }} shown, in this order</span>
            </div>

            @forelse ($entries as $entry)
                <article class="ex-entry {{ $entry->is_active ? '' : 'is-hidden-on-site' }}">
                    <div class="ex-entry-body">
                        <p class="ex-entry-label">{{ $entry->experienceLabel() }}</p>
                        <h5 class="ex-entry-name">{{ $entry->organisation }}</h5>
                        <p class="ex-entry-role">{{ $entry->role }}</p>
                        <p class="ex-entry-desc">{{ Str::limit($entry->description, 180) }}</p>
                        @unless ($entry->is_active)
                            <span class="rs-hidden-badge">Hidden on the site</span>
                        @endunless
                    </div>
                    @include('admin.resume.partials.entry_actions', ['first' => $loop->first, 'last' => $loop->last])
                </article>
            @empty
                <div class="ex-empty">
                    <p>No experience entries yet. The Experiences block is hidden on the site until you add one.</p>
                    <a href="{{ route('admin.experience.create') }}" class="btn btn-primary">+ Add your first entry</a>
                </div>
            @endforelse
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/resume-list.js') }}"></script>
@endpush
