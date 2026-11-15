@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    {{-- The site's Font Awesome Pro bundle, so icon choices look exactly like on the public section. --}}
    <link href="{{ asset('frontend/assets/css/vendor/fontawesome.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/skills.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $isNew = ! $skill->exists;
    $icon = old('icon', $skill->icon);
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.skills.partials.page_header', ['current' => $isNew ? 'Add skill' : 'Edit skill'])

    <form method="POST"
          action="{{ $isNew ? route('admin.skills.store') : route('admin.skills.update', $skill) }}"
          novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== FIELDS + ICON PICKER ===================== -->
            <div class="col-xl-7">
                <div class="sk-card">
                    <h5>{{ $isNew ? 'Add a skill' : 'Edit '.$skill->title }}</h5>
                    <p class="sk-subtitle">One row of the My Skill section.</p>

                    <div class="mb-4">
                        <label for="title">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $skill->title) }}" required maxlength="80" placeholder="e.g. UI / Visual Design" data-preview="title">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-5 mb-4">
                            <label for="done_count">Count</label>
                            <input type="number" class="form-control @error('done_count') is-invalid @enderror" id="done_count" name="done_count" value="{{ old('done_count', $skill->done_count) }}" required min="0" max="9999999" step="1" data-preview="count">
                            @error('done_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-7 mb-4">
                            <label for="done_label">Count label</label>
                            <input type="text" class="form-control @error('done_label') is-invalid @enderror" id="done_label" name="done_label" value="{{ old('done_label', $skill->done_label) }}" maxlength="30" placeholder="Done" data-preview="label">
                            @error('done_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Shown after the number, e.g. "21 Done" or "8 Years". Empty means "Done".</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="description">Description</label>
                            <span class="char-count" data-for="description"></span>
                        </div>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required maxlength="300" data-preview="description" data-char-count>{{ old('description', $skill->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Icon picker -->
                    <div class="sk-icon-picker">
                        <label for="icon">Icon</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text sk-icon-current" aria-hidden="true"><i class="{{ $icon }}" id="sk-icon-current"></i></span>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ $icon }}" required maxlength="60" autocomplete="off" spellcheck="false" data-preview="icon">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text mb-2">Pick one below, or type any Font Awesome class: a style (@foreach ($iconStyles as $style)<code>{{ $style }}</code>@unless ($loop->last), @endunless @endforeach) and a name, e.g. <code>fa-light fa-pen-nib</code>.</div>

                        <label for="sk-icon-search" class="visually-hidden">Search icons</label>
                        <input type="search" class="form-control form-control-sm mb-2" id="sk-icon-search" placeholder="Search icons…" autocomplete="off">

                        <div class="sk-icon-grid" id="sk-icon-grid" role="listbox" aria-label="Suggested icons">
                            @foreach ($iconSuggestions as $name)
                                <button type="button" class="sk-icon-option {{ $icon === 'fa-light fa-'.$name ? 'is-selected' : '' }}" data-icon="fa-light fa-{{ $name }}" data-name="{{ $name }}" title="{{ $name }}" role="option" aria-selected="{{ $icon === 'fa-light fa-'.$name ? 'true' : 'false' }}">
                                    <i class="fa-light fa-{{ $name }}" aria-hidden="true"></i>
                                    <span class="visually-hidden">{{ $name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="sk-switches">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_highlighted" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_highlighted" name="is_highlighted" value="1" @checked(old('is_highlighted', $skill->is_highlighted)) data-preview="highlight">
                            <label class="form-check-label" for="is_highlighted">★ Highlighted (starts with the red background)</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $skill->is_active))>
                            <label class="form-check-label" for="is_active">Show on the site</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== LIVE PREVIEW ===================== -->
            <div class="col-xl-5">
                <div class="sk-preview-wrap">
                    <span class="sk-preview-tag">Preview</span>
                    <div class="sk-preview-card" id="sk-preview-card" aria-hidden="true">
                        <span class="sk-preview-icon"><i class="{{ $icon }}" id="sk-preview-icon"></i></span>
                        <div class="sk-preview-title">
                            <h3 id="sk-preview-title"></h3>
                            <p id="sk-preview-count"></p>
                        </div>
                        <p class="sk-preview-desc" id="sk-preview-desc"></p>
                    </div>
                    <p class="sk-preview-note">The highlighted skill shows with the red background; the others get it when hovered.</p>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-start mt-3">
            <a href="{{ route('admin.skills.index') }}" class="btn btn-light-dark btn-sm">&larr; Back to skills</a>
        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Add skill' : 'Save skill'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/skills.js') }}"></script>
@endpush
