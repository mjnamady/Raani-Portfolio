@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/site-settings.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@use('App\Support\SiteSettings')

@php
    // Old input is only reused when it is text (a crafted array must not break the page).
    $text = fn (mixed $value) => is_string($value) ? $value : '';

    // After a failed save, open the first tab that has an error.
    $tabFields = [
        'identity' => ['site_name', 'site_tagline', 'site_copyright', 'logo', 'logo_light', 'favicon'],
        'social' => ['social', 'social.*'],
        'panel' => ['offcanvas_*', 'offcanvas_image'],
        'footer' => ['footer_*', 'footer_links.*'],
    ];
    $activeTab = collect($tabFields)->search(fn (array $fields) => collect($fields)->contains(
        fn (string $field) => collect($errors->keys())->contains(fn (string $key) => \Illuminate\Support\Str::is($field, $key))
    )) ?: 'identity';

    $oldLinks = old('footer_links', $footer['links']);
    $oldLinks = is_array($oldLinks) ? array_values($oldLinks) : [];
@endphp

<div class="middle-content container-xxl p-0">

    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Site Settings">
            <header class="header navbar navbar-expand-sm">
                <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </a>
                <div class="d-flex breadcrumb-content">
                    <div class="page-header">
                        <div class="page-title"></div>
                        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.banner') }}">Pages</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Site Settings</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto gap-2">
                    <li class="nav-item">
                        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">View site</a>
                    </li>
                </ul>
            </header>
        </div>
    </div>

    <div class="layout-top-spacing">

        <div class="ss-note">
            Contact details (address, emails, phones) are edited once on the
            <a href="{{ route('admin.contact.index') }}">Contact Page</a>
            and appear in the menu panel, the banner, the Contact page and the footer.
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" novalidate data-track-changes>
            @csrf
            @method('PUT')

            <ul class="nav nav-pills ss-tabs" role="tablist">
                @foreach (['identity' => 'Identity', 'social' => 'Social links', 'panel' => 'Menu panel', 'footer' => 'Footer'] as $tab => $label)
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link {{ $activeTab === $tab ? 'active' : '' }} @if (collect($tabFields[$tab])->contains(fn ($field) => collect($errors->keys())->contains(fn ($key) => \Illuminate\Support\Str::is($field, $key)))) has-error @endif" id="tab-{{ $tab }}" data-bs-toggle="pill" data-bs-target="#pane-{{ $tab }}" role="tab" aria-controls="pane-{{ $tab }}" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}">{{ $label }}</button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content">

                <!-- ===================== IDENTITY ===================== -->
                <div class="tab-pane fade {{ $activeTab === 'identity' ? 'show active' : '' }}" id="pane-identity" role="tabpanel" aria-labelledby="tab-identity">
                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="ss-card">
                                <div class="mb-3">
                                    <label for="site_name">Site name</label>
                                    <input type="text" class="form-control @error('site_name') is-invalid @enderror" id="site_name" name="site_name" value="{{ $text(old('site_name', $site['name'])) }}" required maxlength="60" data-preview="site-name">
                                    <div class="form-text">Shown in the browser tab after each page title, and as {site} in the copyright.</div>
                                    @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="site_tagline">Site description</label>
                                    <input type="text" class="form-control @error('site_tagline') is-invalid @enderror" id="site_tagline" name="site_tagline" value="{{ $text(old('site_tagline', $site['tagline'])) }}" maxlength="160">
                                    <div class="form-text">The default description search engines show for the site (160 characters at most).</div>
                                    @error('site_tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label for="site_copyright">Copyright text</label>
                                    <input type="text" class="form-control @error('site_copyright') is-invalid @enderror" id="site_copyright" name="site_copyright" value="{{ $text(old('site_copyright', $site['copyright'])) }}" required maxlength="120">
                                    <div class="form-text"><code>{year}</code> becomes the current year and <code>{site}</code> the site name.</div>
                                    @error('site_copyright') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                @foreach (['logo', 'logo_light', 'favicon'] as $image)
                                    @include('admin.settings.partials.image_field', ['image' => $image, 'config' => $images[$image]])
                                @endforeach
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="ss-preview-label">Preview</div>
                            <div class="ss-browser" aria-hidden="true">
                                <div class="ss-browser-tab">
                                    <img src="{{ $images['favicon']['url'] }}" alt="" data-image-preview="favicon">
                                    <span>Blog · <span data-preview-target="site-name">{{ $text(old('site_name', $site['name'])) }}</span></span>
                                </div>
                                <div class="ss-browser-page">
                                    <img class="ss-browser-logo" src="{{ $images['logo']['url'] }}" alt="" data-image-preview="logo">
                                    <span class="ss-browser-menu">Home · About · Blog · Projects · Contact</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== SOCIAL ===================== -->
                <div class="tab-pane fade {{ $activeTab === 'social' ? 'show active' : '' }}" id="pane-social" role="tabpanel" aria-labelledby="tab-social">
                    <div class="ss-card">
                        <p class="ss-subtitle">Full addresses starting with https://. Empty links are hidden; the icons appear in the header, the menu panel, the mobile menu and the footer.</p>
                        <div class="row g-3">
                            @foreach (SiteSettings::SOCIAL_NETWORKS as $key => $network)
                                <div class="col-md-6">
                                    <label for="social-{{ $key }}">{{ $network['label'] }}</label>
                                    <div class="ss-social-field">
                                        <span class="ss-social-dot" data-social-dot="{{ $key }}" aria-hidden="true"></span>
                                        <input type="url" class="form-control @error('social.'.$key) is-invalid @enderror" id="social-{{ $key }}" name="social[{{ $key }}]" value="{{ $text(old('social.'.$key, $social[$key] ?? '')) }}" maxlength="255" placeholder="https://" data-social-input="{{ $key }}">
                                    </div>
                                    @error('social.'.$key) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div>
                        @error('social') <div class="ss-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- ===================== MENU PANEL ===================== -->
                <div class="tab-pane fade {{ $activeTab === 'panel' ? 'show active' : '' }}" id="pane-panel" role="tabpanel" aria-labelledby="tab-panel">
                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="ss-card">
                                <p class="ss-subtitle">The side panel that opens from the menu button on large screens.</p>
                                <div class="mb-3">
                                    <label for="offcanvas_heading">Heading</label>
                                    <input type="text" class="form-control @error('offcanvas_heading') is-invalid @enderror" id="offcanvas_heading" name="offcanvas_heading" value="{{ $text(old('offcanvas_heading', $offcanvas['heading'])) }}" maxlength="120" data-preview="panel-heading">
                                    @error('offcanvas_heading') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="offcanvas_bio">Text</label>
                                        <span class="char-count" data-for="offcanvas_bio"></span>
                                    </div>
                                    <textarea class="form-control @error('offcanvas_bio') is-invalid @enderror" id="offcanvas_bio" name="offcanvas_bio" rows="3" maxlength="300" data-char-count data-preview="panel-bio">{{ $text(old('offcanvas_bio', $offcanvas['bio'])) }}</textarea>
                                    @error('offcanvas_bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="row g-3">
                                    @foreach (['phone_label' => 'Phone label', 'email_label' => 'Email label', 'address_label' => 'Address label', 'social_label' => 'Social links label'] as $field => $label)
                                        <div class="col-md-6">
                                            <label for="offcanvas_{{ $field }}">{{ $label }}</label>
                                            <input type="text" class="form-control @error('offcanvas_'.$field) is-invalid @enderror" id="offcanvas_{{ $field }}" name="offcanvas_{{ $field }}" value="{{ $text(old('offcanvas_'.$field, $offcanvas[$field])) }}" required maxlength="30" data-preview="panel-{{ $field }}">
                                            @error('offcanvas_'.$field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                @include('admin.settings.partials.image_field', ['image' => 'offcanvas_image', 'config' => $images['offcanvas_image']])
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="ss-preview-label">Preview</div>
                            <div class="ss-panel" aria-hidden="true">
                                <img class="ss-panel-photo" src="{{ $images['offcanvas_image']['url'] }}" alt="" data-image-preview="offcanvas_image">
                                <strong data-preview-target="panel-heading">{{ $text(old('offcanvas_heading', $offcanvas['heading'])) }}</strong>
                                <p data-preview-target="panel-bio">{{ $text(old('offcanvas_bio', $offcanvas['bio'])) }}</p>
                                <ul>
                                    <li><small data-preview-target="panel-phone_label">{{ $offcanvas['phone_label'] }}</small> your phone</li>
                                    <li><small data-preview-target="panel-email_label">{{ $offcanvas['email_label'] }}</small> your email</li>
                                    <li><small data-preview-target="panel-address_label">{{ $offcanvas['address_label'] }}</small> your address</li>
                                </ul>
                                <small class="ss-panel-social" data-preview-target="panel-social_label">{{ $offcanvas['social_label'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================== FOOTER ===================== -->
                <div class="tab-pane fade {{ $activeTab === 'footer' ? 'show active' : '' }}" id="pane-footer" role="tabpanel" aria-labelledby="tab-footer">
                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="ss-card">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-5">
                                        <label for="footer_tagline_highlight">Highlighted words</label>
                                        <input type="text" class="form-control @error('footer_tagline_highlight') is-invalid @enderror" id="footer_tagline_highlight" name="footer_tagline_highlight" value="{{ $text(old('footer_tagline_highlight', $footer['tagline_highlight'])) }}" maxlength="30" data-preview="footer-highlight">
                                        @error('footer_tagline_highlight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-7">
                                        <label for="footer_tagline">Rest of the tagline</label>
                                        <input type="text" class="form-control @error('footer_tagline') is-invalid @enderror" id="footer_tagline" name="footer_tagline" value="{{ $text(old('footer_tagline', $footer['tagline'])) }}" maxlength="60" data-preview="footer-tagline">
                                        @error('footer_tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="footer_links_title">Quick links title</label>
                                        <input type="text" class="form-control @error('footer_links_title') is-invalid @enderror" id="footer_links_title" name="footer_links_title" value="{{ $text(old('footer_links_title', $footer['links_title'])) }}" required maxlength="40" data-preview="footer-links-title">
                                        @error('footer_links_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="footer_contact_title">Contact column title</label>
                                        <input type="text" class="form-control @error('footer_contact_title') is-invalid @enderror" id="footer_contact_title" name="footer_contact_title" value="{{ $text(old('footer_contact_title', $footer['contact_title'])) }}" required maxlength="40">
                                        @error('footer_contact_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <label class="d-block">Quick links</label>
                                <p class="ss-subtitle">A page on this site such as <code>/blog</code>, or a full address starting with https://. Up to {{ SiteSettings::MAX_FOOTER_LINKS }} links; empty rows are ignored.</p>
                                <div class="ss-links" data-footer-links>
                                    @for ($i = 0; $i < SiteSettings::MAX_FOOTER_LINKS; $i++)
                                        @php
                                            $link = is_array($oldLinks[$i] ?? null) ? $oldLinks[$i] : [];
                                        @endphp
                                        <div class="ss-link-row" data-link-row>
                                            <span class="ss-link-handle" aria-hidden="true">{{ $i + 1 }}</span>
                                            <label class="visually-hidden" for="footer-link-label-{{ $i }}">Link {{ $i + 1 }} text</label>
                                            <input type="text" class="form-control form-control-sm @error('footer_links.'.$i.'.label') is-invalid @enderror" id="footer-link-label-{{ $i }}" name="footer_links[{{ $i }}][label]" value="{{ $text($link['label'] ?? '') }}" maxlength="30" placeholder="Link text" data-link-label>
                                            <label class="visually-hidden" for="footer-link-url-{{ $i }}">Link {{ $i + 1 }} address</label>
                                            <input type="text" class="form-control form-control-sm @error('footer_links.'.$i.'.url') is-invalid @enderror" id="footer-link-url-{{ $i }}" name="footer_links[{{ $i }}][url]" value="{{ $text($link['url'] ?? '') }}" maxlength="255" placeholder="/page or https://" data-link-url>
                                            <button type="button" class="ss-icon-btn" data-link-move="up" aria-label="Move link {{ $i + 1 }} up">↑</button>
                                            <button type="button" class="ss-icon-btn" data-link-move="down" aria-label="Move link {{ $i + 1 }} down">↓</button>
                                            <button type="button" class="ss-icon-btn ss-delete" data-link-clear aria-label="Clear link {{ $i + 1 }}">✕</button>
                                            @foreach ($errors->get('footer_links.'.$i.'.*') as $messages)
                                                @foreach ((array) $messages as $message)
                                                    <div class="ss-error w-100">{{ $message }}</div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    @endfor
                                </div>
                                <button type="button" class="btn btn-light-primary btn-sm mt-2 d-none" data-link-add>+ Add a link</button>
                                @error('footer_links') <div class="ss-error">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="ss-preview-label">Preview</div>
                            <div class="ss-footer" aria-hidden="true">
                                <p class="ss-footer-tagline"><span data-preview-target="footer-highlight">{{ $text(old('footer_tagline_highlight', $footer['tagline_highlight'])) }}</span> <span data-preview-target="footer-tagline">{{ $text(old('footer_tagline', $footer['tagline'])) }}</span></p>
                                <strong data-preview-target="footer-links-title">{{ $text(old('footer_links_title', $footer['links_title'])) }}</strong>
                                <ul data-footer-preview></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save site settings'])
        </form>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/site-settings.js') }}"></script>
@endpush
