@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/admin-forms.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/banner-section.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    // Which fields live on which tab, so a failed save opens the tab with the first error.
    $tabs = [
        'headline' => ['label' => 'Headline', 'hint' => 'Greeting, name and role', 'fields' => ['greeting', 'name', 'role']],
        'biography' => ['label' => 'Biography', 'hint' => 'The text beside the portrait', 'fields' => ['biography_heading', 'biography']],
        'contact' => ['label' => 'Contact', 'hint' => 'Heading above the contact details', 'fields' => ['contact_heading']],
        'counters' => ['label' => 'Counters', 'hint' => 'Up to 3 animated numbers', 'fields' => ['stats']],
        'portrait' => ['label' => 'Portrait', 'hint' => 'Image and alt text', 'fields' => ['image', 'image_alt']],
    ];

    $errorKeys = collect($errors->keys());
    $tabHasErrors = collect($tabs)->map(fn ($tab) => $errorKeys->contains(
        fn ($key) => collect($tab['fields'])->contains(fn ($field) => $key === $field || str_starts_with($key, $field.'.'))
    ));
    $activeTab = $tabHasErrors->filter()->keys()->first() ?? 'headline';

    $isVisible = (bool) old('is_active', $banner->is_active);

    // The real upload limit: the app's own, lowered by PHP's upload_max_filesize when smaller.
    $maxImageBytes = \App\Support\UploadLimit::bytes(\App\Http\Requests\Admin\UpdateBannerSectionRequest::MAX_IMAGE_KILOBYTES);
    $maxImageLabel = \App\Support\UploadLimit::label(\App\Http\Requests\Admin\UpdateBannerSectionRequest::MAX_IMAGE_KILOBYTES);
@endphp

<div class="middle-content container-xxl p-0">

    <!--  BEGIN BREADCRUMBS  -->
    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Banner Section">
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
                                <li class="breadcrumb-item active" aria-current="page">Banner Section</li>
                            </ol>
                        </nav>

                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto">
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

    <form method="POST" action="{{ route('admin.banner.update') }}" enctype="multipart/form-data" novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @method('PATCH')

        <!-- ===================== LIVE STAGE: a miniature of the public banner ===================== -->
        <div class="bn-stage {{ $isVisible ? '' : 'is-off' }}" id="bn-stage" aria-label="Live preview of the banner">
            <div class="bn-stage-toolbar">
                <span class="bn-stage-tag">Live preview</span>

                <div class="bn-visibility">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked($isVisible)>
                        <label class="form-check-label" for="is_active" id="bn-visibility-label">{{ $isVisible ? 'Visible on home page' : 'Hidden from home page' }}</label>
                    </div>
                </div>
            </div>

            <h2 class="bn-stage-title">
                <span data-show="greeting" data-placeholder="Greeting">{{ $banner->greeting }}</span>
                <span data-show="name" data-placeholder="Your name">{{ $banner->name }}</span><br>
                <span data-show="role" data-placeholder="your role">{{ $banner->role }}</span>
            </h2>

            <div class="bn-stage-grid">
                <ul class="bn-stage-stats" id="bn-stage-stats">
                    @foreach ($statRows as $i => $row)
                        <li data-stat-preview="{{ $i }}">
                            <strong></strong>
                            <span></span>
                        </li>
                    @endforeach
                </ul>

                <div class="bn-stage-portrait">
                    <img id="banner-image-preview"
                         src="{{ $banner->image_url }}"
                         data-original="{{ $banner->image_url }}"
                         alt="Banner portrait preview">
                    <span class="badge badge-primary bn-new-badge">New</span>
                </div>

                <div class="bn-stage-info">
                    <h6 data-show="biography_heading" data-placeholder="Biography :">{{ $banner->biography_heading }}</h6>
                    <p data-show="biography" data-placeholder="Your biography">{{ $banner->biography }}</p>

                    <h6 data-show="contact_heading" data-placeholder="Contact :">{{ $banner->contact_heading }}</h6>
                    @foreach (array_filter([$contact['address'], $contact['email'], $contact['phone']]) as $line)
                        <p>{{ $line }}</p>
                    @endforeach
                </div>
            </div>

            <div class="bn-stage-off-note">Hidden: visitors will not see this banner.</div>
        </div>

        <!-- ===================== TABBED EDITOR ===================== -->
        <div class="bn-editor">
            <div class="nav flex-column bn-tabs" role="tablist" aria-orientation="vertical">
                @foreach ($tabs as $key => $tab)
                    <button type="button"
                            class="bn-tab {{ $activeTab === $key ? 'active' : '' }} {{ $tabHasErrors[$key] ? 'has-error' : '' }}"
                            id="bn-tab-{{ $key }}"
                            data-bs-toggle="pill"
                            data-bs-target="#bn-pane-{{ $key }}"
                            role="tab"
                            aria-controls="bn-pane-{{ $key }}"
                            aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                        <span class="bn-tab-step">{{ $loop->iteration }}</span>
                        <span class="bn-tab-text">
                            <strong>{{ $tab['label'] }}</strong>
                            <small>{{ $tab['hint'] }}</small>
                        </span>
                        @if ($tabHasErrors[$key])
                            <span class="bn-tab-error" aria-label="Has errors"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="tab-content bn-panes">

                <!-- Headline -->
                <div class="tab-pane fade {{ $activeTab === 'headline' ? 'show active' : '' }}" id="bn-pane-headline" role="tabpanel" aria-labelledby="bn-tab-headline" tabindex="0">
                    <h5>Headline</h5>
                    <p class="bn-pane-subtitle">The large title: <em>&lt;greeting&gt; &lt;name&gt;</em>, then <em>&lt;role&gt;</em> on the next line.</p>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="greeting">Greeting <span class="char-count" data-for="greeting"></span></label>
                            <input type="text" class="form-control @error('greeting') is-invalid @enderror" id="greeting" name="greeting" value="{{ old('greeting', $banner->greeting) }}" required maxlength="60" data-char-count data-bind="greeting">
                            @error('greeting')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8 mb-4">
                            <label for="name">Name <span class="char-count" data-for="name"></span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $banner->name) }}" required maxlength="80" data-char-count data-bind="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-0">
                            <label for="role">Role <span class="char-count" data-for="role"></span></label>
                            <input type="text" class="form-control @error('role') is-invalid @enderror" id="role" name="role" value="{{ old('role', $banner->role) }}" required maxlength="80" data-char-count data-bind="role">
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">e.g. "a Product Designer" or "a Full-Stack Developer".</div>
                        </div>
                    </div>
                </div>

                <!-- Biography -->
                <div class="tab-pane fade {{ $activeTab === 'biography' ? 'show active' : '' }}" id="bn-pane-biography" role="tabpanel" aria-labelledby="bn-tab-biography" tabindex="0">
                    <h5>Biography</h5>
                    <p class="bn-pane-subtitle">The short text on the right of the portrait.</p>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="biography_heading">Heading</label>
                            <input type="text" class="form-control @error('biography_heading') is-invalid @enderror" id="biography_heading" name="biography_heading" value="{{ old('biography_heading', $banner->biography_heading) }}" required maxlength="40" data-bind="biography_heading">
                            @error('biography_heading')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-0">
                            <label for="biography">Biography <span class="char-count" data-for="biography"></span></label>
                            <textarea class="form-control @error('biography') is-invalid @enderror" id="biography" name="biography" rows="5" required maxlength="600" data-char-count data-bind="biography">{{ old('biography', $banner->biography) }}</textarea>
                            @error('biography')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="tab-pane fade {{ $activeTab === 'contact' ? 'show active' : '' }}" id="bn-pane-contact" role="tabpanel" aria-labelledby="bn-tab-contact" tabindex="0">
                    <h5>Contact block</h5>
                    <p class="bn-pane-subtitle">
                        The address, first email and first phone below this heading are the site-wide contact details,
                        edited once on the <a href="{{ route('admin.contact.index') }}">Contact Page</a>. The block is hidden when none are set.
                    </p>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="contact_heading">Heading</label>
                            <input type="text" class="form-control @error('contact_heading') is-invalid @enderror" id="contact_heading" name="contact_heading" value="{{ old('contact_heading', $banner->contact_heading) }}" required maxlength="40" data-bind="contact_heading">
                            @error('contact_heading')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-0">
                            <label>Shown below it</label>
                            <ul class="list-unstyled mb-0 small">
                                <li>{{ $contact['address'] ?? 'No address set' }}</li>
                                <li>{{ $contact['email'] ?? 'No email set' }}</li>
                                <li>{{ $contact['phone'] ?? 'No phone set' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Counters -->
                <div class="tab-pane fade {{ $activeTab === 'counters' ? 'show active' : '' }}" id="bn-pane-counters" role="tabpanel" aria-labelledby="bn-tab-counters" tabindex="0">
                    <h5>Counters</h5>
                    <p class="bn-pane-subtitle">Up to 3 animated numbers on the left of the portrait. Leave a row empty to hide it.</p>

                    @error('stats')
                        <div class="alert alert-light-danger mb-3">{{ $message }}</div>
                    @enderror

                    <div class="bn-stat-list">
                        @foreach ($statRows as $i => $row)
                            <div class="bn-stat-row" data-stat-row="{{ $i }}">
                                <span class="bn-stat-index">{{ $i + 1 }}</span>
                                <div class="bn-stat-fields">
                                    <div class="bn-stat-value">
                                        <label for="stats_{{ $i }}_value">Number</label>
                                        <input type="number" class="form-control @error("stats.$i.value") is-invalid @enderror" id="stats_{{ $i }}_value" name="stats[{{ $i }}][value]" value="{{ old("stats.$i.value", $row?->value) }}" min="0" max="9999999" step="1" data-stat="value">
                                        @error("stats.$i.value")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="bn-stat-suffix">
                                        <label for="stats_{{ $i }}_suffix">Suffix</label>
                                        <input type="text" class="form-control @error("stats.$i.suffix") is-invalid @enderror" id="stats_{{ $i }}_suffix" name="stats[{{ $i }}][suffix]" value="{{ old("stats.$i.suffix", $row?->suffix) }}" maxlength="5" placeholder="k, +" data-stat="suffix">
                                        @error("stats.$i.suffix")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="bn-stat-label">
                                        <label for="stats_{{ $i }}_label">Label</label>
                                        <input type="text" class="form-control @error("stats.$i.label") is-invalid @enderror" id="stats_{{ $i }}_label" name="stats[{{ $i }}][label]" value="{{ old("stats.$i.label", $row?->label) }}" maxlength="60" placeholder="e.g. Complete project" data-stat="label">
                                        @error("stats.$i.label")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Portrait -->
                <div class="tab-pane fade {{ $activeTab === 'portrait' ? 'show active' : '' }}" id="bn-pane-portrait" role="tabpanel" aria-labelledby="bn-tab-portrait" tabindex="0">
                    <h5>Portrait</h5>
                    <p class="bn-pane-subtitle">Shown in the middle of the banner. The preview above updates as soon as you choose a file.</p>

                    <div class="mb-4">
                        <label for="image">Upload a new portrait</label>
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/png,image/webp"
                               data-max-bytes="{{ $maxImageBytes }}"
                               data-max-label="{{ $maxImageLabel }}">
                        <div id="banner-image-client-error" class="invalid-feedback d-none"></div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            JPG, PNG or WebP, up to {{ $maxImageLabel }}. It is resized to {{ \App\Models\BannerSection::IMAGE_WIDTH }}&times;{{ \App\Models\BannerSection::IMAGE_HEIGHT }} px.
                            A PNG with a transparent background looks best.
                        </div>
                        <button type="button" id="banner-image-reset" class="btn btn-light-dark btn-sm mt-2 d-none">Undo image change</button>
                    </div>

                    <div class="mb-0">
                        <label for="image_alt">Image description (alt text)</label>
                        <input type="text" class="form-control @error('image_alt') is-invalid @enderror" id="image_alt" name="image_alt" value="{{ old('image_alt', $banner->image_alt) }}" required maxlength="150">
                        @error('image_alt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Read by screen readers and search engines, e.g. "Portrait of {{ $banner->name }}".</div>
                    </div>
                </div>

            </div>
        </div>

        @include('admin.layout.components.save_bar', ['label' => 'Save banner'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/banner-section.js') }}"></script>
@endpush
