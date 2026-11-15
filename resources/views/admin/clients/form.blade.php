@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/clients.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $isNew = ! $client->exists;
    $maxKilobytes = \App\Http\Requests\Admin\StoreClientRequest::MAX_LOGO_KILOBYTES;
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.clients.partials.page_header', ['current' => $isNew ? 'Add logo' : 'Edit logo'])

    <form method="POST"
          action="{{ $isNew ? route('admin.clients.store') : route('admin.clients.update', $client) }}"
          enctype="multipart/form-data" novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @if (! $isNew)
            @method('PUT')
        @endif

        <div class="row g-4">

            <!-- ===================== LOGO + PREVIEW ===================== -->
            <div class="col-xl-6">
                <div class="cl-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Logo</h5>
                        <div class="cl-bg-switch" role="group" aria-label="Preview background">
                            <button type="button" class="is-active" data-preview-bg="dark">Dark</button>
                            <button type="button" data-preview-bg="light">Light</button>
                        </div>
                    </div>

                    <input type="file" class="visually-hidden" id="logo" name="logo" accept="image/png,image/webp,image/jpeg"
                           data-max-bytes="{{ \App\Support\UploadLimit::bytes($maxKilobytes) }}"
                           data-max-label="{{ \App\Support\UploadLimit::label($maxKilobytes) }}">
                    <label for="logo" class="cl-drop is-dark @error('logo') is-invalid @enderror" id="cl-drop">
                        @if ($client->logo)
                            <img id="cl-logo-preview" src="{{ $client->logo_url }}" data-original="{{ $client->logo_url }}" alt="Logo preview">
                        @else
                            <img id="cl-logo-preview" src="" data-original="" alt="Logo preview" hidden>
                        @endif
                        <span class="cl-drop-empty" id="cl-drop-empty" @if ($client->logo) hidden @endif>
                            <strong>Drop a logo here</strong>
                            <span>or click to browse</span>
                        </span>
                        <span class="badge badge-primary cl-new-badge">New</span>
                    </label>

                    <div id="cl-logo-error" class="invalid-feedback d-none"></div>
                    @error('logo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">PNG, WebP or JPG · max {{ \App\Support\UploadLimit::label($maxKilobytes) }} · scaled to fit {{ \App\Models\Client::LOGO_MAX_WIDTH }}&times;{{ \App\Models\Client::LOGO_MAX_HEIGHT }}, never cropped</small>
                        <button type="button" id="cl-logo-reset" class="btn btn-light-dark btn-sm d-none">Undo</button>
                    </div>
                </div>
            </div>

            <!-- ===================== DETAILS ===================== -->
            <div class="col-xl-6">
                <div class="cl-card">
                    <h5>{{ $isNew ? 'Add a supported company' : 'Edit '.$client->name }}</h5>
                    <p class="cl-subtitle">The name is used as the logo's alt text for screen readers and search engines.</p>

                    <div class="mb-4">
                        <label for="name">Company name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $client->name) }}" required maxlength="80">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="url">Website <span class="text-muted">(optional)</span></label>
                        <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $client->url) }}" maxlength="255" placeholder="https://example.com" inputmode="url">
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">When set, the logo links to it and opens in a new tab.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $client->is_active))>
                        <label class="form-check-label" for="is_active">Show on the site</label>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-start mt-3">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-light-dark btn-sm">&larr; Back to the logo wall</a>
        </div>

        @include('admin.layout.components.save_bar', ['label' => $isNew ? 'Add logo' : 'Save logo'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/clients.js') }}"></script>
@endpush
