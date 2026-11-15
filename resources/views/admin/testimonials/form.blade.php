@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/testimonials.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $isNew = ! $testimonial->exists;
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.testimonials.partials.page_header', ['current' => $isNew ? 'Add testimonial' : 'Edit testimonial'])

    <form method="POST"
          action="{{ $isNew ? route('admin.testimonials.store') : route('admin.testimonials.update', $testimonial) }}"
          novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== FIELDS ===================== -->
            <div class="col-xl-6">
                <div class="tm-form-card">
                    <h5>{{ $isNew ? 'Add a testimonial' : 'Edit the testimonial from '.$testimonial->name }}</h5>
                    <p class="tm-subtitle">One slide of the Testimonial section.</p>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="quote">Quote</label>
                            <span class="char-count" data-for="quote"></span>
                        </div>
                        <textarea class="form-control @error('quote') is-invalid @enderror" id="quote" name="quote" rows="5" required maxlength="500" placeholder="What did they say about working with you?" data-preview="quote" data-char-count>{{ old('quote', $testimonial->quote) }}</textarea>
                        @error('quote')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Shown as one paragraph; about 150–250 characters fits the card best.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="name">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $testimonial->name) }}" required maxlength="80" placeholder="e.g. Cameron Williamson" data-preview="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="role">Role or company <span class="tm-optional">(optional)</span></label>
                            <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role', $testimonial->role) }}" maxlength="80" placeholder="e.g. UI/UX Designer, Acme" data-preview="role">
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active))>
                        <label class="form-check-label" for="is_active">Show on the site</label>
                    </div>
                </div>
            </div>

            <!-- ===================== LIVE PREVIEW ===================== -->
            <div class="col-xl-6">
                <div class="tm-preview-wrap">
                    <span class="tm-preview-tag">Preview</span>
                    <div class="tm-preview-card" aria-hidden="true">
                        <img src="{{ asset('frontend/assets/images/icons/quote.svg') }}" alt="" class="tm-preview-icon">
                        <p class="tm-preview-quote" id="tm-preview-quote"></p>
                        <h3 id="tm-preview-name"></h3>
                        <p class="tm-preview-role" id="tm-preview-role"></p>
                    </div>
                    <p class="tm-preview-note">How the card looks in the slider on the home page.</p>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-start mt-3">
            <a href="{{ route('admin.testimonials.index') }}" class="btn btn-light-dark btn-sm">&larr; Back to testimonials</a>
        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Add testimonial' : 'Save testimonial'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/testimonials.js') }}"></script>
@endpush
