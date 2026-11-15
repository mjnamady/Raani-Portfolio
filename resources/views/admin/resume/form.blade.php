@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/resume-shared.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/resume-form.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $isEducation = $config['type'] === \App\Models\ResumeEntry::TYPE_EDUCATION;
    $isNew = ! $entry->exists;
    $heading = ($isNew ? 'Add ' : 'Edit ').$config['singular'];
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.resume.partials.page_header', ['current' => $isNew ? 'Add entry' : 'Edit entry'])

    <form method="POST"
          action="{{ $isNew ? route($config['route'].'.store') : route($config['route'].'.update', $entry) }}"
          novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== FIELDS ===================== -->
            <div class="col-xl-7">
                <div class="rf-card">
                    <h5>{{ ucfirst($heading) }}</h5>
                    <p class="rf-subtitle">
                        {{ $isEducation ? 'A card in the two-column Education grid.' : 'An entry in the Experiences list next to the photo.' }}
                    </p>

                    @if ($isEducation)
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label for="title">Title <span class="char-count" data-for="title"></span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $entry->title) }}" required maxlength="80" placeholder="e.g. BSc Computer Science" data-char-count data-bind="title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="period">Years</label>
                                <input type="text" class="form-control @error('period') is-invalid @enderror" id="period" name="period" value="{{ old('period', $entry->period) }}" required maxlength="40" placeholder="2005-2009" data-bind="period">
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-4">
                                <label for="organisation">Institution <span class="text-muted">(optional)</span></label>
                                <input type="text" class="form-control @error('organisation') is-invalid @enderror" id="organisation" name="organisation" value="{{ old('organisation', $entry->organisation) }}" maxlength="120" placeholder="e.g. University of Lagos" data-bind="organisation">
                                @error('organisation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Shown after the title: "Title · Institution".</div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-7 mb-4">
                                <label for="organisation">Name <span class="char-count" data-for="organisation"></span></label>
                                <input type="text" class="form-control @error('organisation') is-invalid @enderror" id="organisation" name="organisation" value="{{ old('organisation', $entry->organisation) }}" required maxlength="120" placeholder="Company, client or project" data-char-count data-bind="organisation">
                                @error('organisation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-4">
                                <label for="role">Role</label>
                                <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role', $entry->role) }}" required maxlength="80" placeholder="e.g. UI/UX Designer" data-bind="role">
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-4">
                                <label for="period">Label <span class="text-muted">(optional)</span></label>
                                <input type="text" class="form-control @error('period') is-invalid @enderror" id="period" name="period" value="{{ old('period', $entry->period) }}" maxlength="40" placeholder="e.g. 2019 – Present" data-bind="period">
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The small red text above the name. Leave empty to show "Experience".</div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="description">Description <span class="char-count" data-for="description"></span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required maxlength="400" data-char-count data-bind="description">{{ old('description', $entry->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $entry->is_active))>
                        <label class="form-check-label" for="is_active">Show on the site</label>
                    </div>
                </div>
            </div>

            <!-- ===================== LIVE PREVIEW ===================== -->
            <div class="col-xl-5">
                <div class="rf-preview-wrap">
                    <span class="rf-preview-tag">Preview</span>

                    @if ($isEducation)
                        <div class="rf-edu-card" aria-hidden="true">
                            <p class="rf-edu-sub"><span data-show="title" data-placeholder="Title"></span><span class="rf-edu-org" data-show-join="organisation"></span></p>
                            <p class="rf-edu-years" data-show="period" data-placeholder="2005-2009"></p>
                            <p class="rf-edu-para" data-show="description" data-placeholder="A short description of this period."></p>
                        </div>
                    @else
                        <div class="rf-exp-card" aria-hidden="true">
                            <p class="rf-exp-label" data-show="period" data-placeholder="Experience"></p>
                            <p class="rf-exp-name" data-show="organisation" data-placeholder="Name"></p>
                            <p class="rf-exp-role" data-show="role" data-placeholder="Role"></p>
                            <p class="rf-exp-para" data-show="description" data-placeholder="A short description of what you did."></p>
                        </div>
                    @endif

                    <p class="rf-preview-note">Placeholder text in grey is not shown on the site.</p>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-start mt-3">
            <a href="{{ route($config['route'].'.index') }}" class="btn btn-light-dark btn-sm">&larr; Back to {{ strtolower($config['label']) }}</a>
        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Add entry' : 'Save entry'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/resume-form.js') }}"></script>
@endpush
