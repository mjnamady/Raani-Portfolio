@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/contact-page.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $maxLines = \App\Support\ContactDetails::MAX_LINES;

    // One entry per public card (icons are inline SVGs below, since the admin does not load Font Awesome).
    $cards = [
        [
            'key' => 'address', 'titleField' => 'address_title', 'list' => 'address_lines',
            'type' => 'text', 'maxlength' => 120, 'lineLabel' => 'Address line',
            'placeholders' => ['Street and number', 'City, country (optional)'],
        ],
        [
            'key' => 'email', 'titleField' => 'email_title', 'list' => 'emails',
            'type' => 'email', 'maxlength' => 150, 'lineLabel' => 'Email address',
            'placeholders' => ['hello@yourdomain.com', 'Second address (optional)'],
        ],
        [
            'key' => 'phone', 'titleField' => 'phone_title', 'list' => 'phones',
            'type' => 'tel', 'maxlength' => 40, 'lineLabel' => 'Phone number',
            'placeholders' => ['+1 (555) 010-0000', 'Second number (optional)'],
        ],
    ];

    // Old input is only reused when it is text (a crafted array must not break the page).
    $text = fn (mixed $value) => is_string($value) ? $value : '';
@endphp

<div class="middle-content container-xxl p-0">

    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Contact Page">
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
                                <li class="breadcrumb-item active" aria-current="page">Contact Page</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto gap-2">
                    <li class="nav-item">
                        <a href="{{ route('contact.index') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">View Contact page</a>
                    </li>
                </ul>
            </header>
        </div>
    </div>

    <div class="layout-top-spacing">

        <form method="POST" action="{{ route('admin.contact.update') }}" novalidate data-track-changes>
            @csrf
            @method('PUT')

            <!-- ===================== CONTACT CARDS ===================== -->
            <div class="cp-intro">
                <h5>Contact details</h5>
                <p>The three cards at the top of the Contact page. Each card shows up to {{ $maxLines }} lines; a card with no lines is hidden on the site.</p>
            </div>

            <div class="row g-4">
                @foreach ($cards as $card)
                    @php
                        $lines = old($card['list'], $contact[$card['list']]);
                        $lines = is_array($lines) ? array_values($lines) : [];
                        $cardErrors = collect($errors->get($card['list']))
                            ->merge(collect($errors->get($card['list'].'.*'))->flatten())
                            ->unique();
                    @endphp
                    <div class="col-xl-4 col-md-6">
                        <section class="cp-card @if ($cardErrors->isNotEmpty() || $errors->has($card['titleField'])) has-error @endif" aria-labelledby="cp-title-{{ $card['key'] }}" data-card="{{ $card['key'] }}">

                            <!-- Live preview, styled like the public card -->
                            <div class="cp-preview" aria-hidden="true">
                                <span class="cp-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        @switch($card['key'])
                                            @case('address')
                                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"/>
                                                @break
                                            @case('email')
                                                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"/><path d="M3 7l9 6l9 -6"/>
                                                @break
                                            @default
                                                <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"/>
                                        @endswitch
                                    </svg>
                                </span>
                                <span class="cp-preview-title" data-preview-title>{{ $text(old($card['titleField'], $contact[$card['titleField']])) }}</span>
                                <span class="cp-preview-lines" data-preview-lines></span>
                                <span class="cp-preview-empty" data-preview-empty>Hidden on the site until it has a line</span>
                            </div>

                            <div class="cp-fields">
                                <label for="{{ $card['titleField'] }}" id="cp-title-{{ $card['key'] }}">Card title</label>
                                <input type="text" class="form-control @error($card['titleField']) is-invalid @enderror" id="{{ $card['titleField'] }}" name="{{ $card['titleField'] }}" value="{{ $text(old($card['titleField'], $contact[$card['titleField']])) }}" required maxlength="40" data-title-input>
                                @error($card['titleField'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                @for ($i = 0; $i < $maxLines; $i++)
                                    <label for="{{ $card['list'] }}-{{ $i }}" class="mt-3">{{ $card['lineLabel'] }} {{ $i + 1 }}</label>
                                    <input type="{{ $card['type'] }}" class="form-control" id="{{ $card['list'] }}-{{ $i }}" name="{{ $card['list'] }}[]" value="{{ $text($lines[$i] ?? '') }}" maxlength="{{ $card['maxlength'] }}" placeholder="{{ $card['placeholders'][$i] }}" data-line-input>
                                @endfor

                                @foreach ($cardErrors as $message)
                                    <div class="cp-error">{{ $message }}</div>
                                @endforeach
                            </div>
                        </section>
                    </div>
                @endforeach
            </div>

            <!-- ===================== GET IN TOUCH ===================== -->
            <div class="cp-touch">
                <div class="cp-touch-head">
                    <h5>"Get in touch"</h5>
                    <p>The heading and text next to the contact form, on both the Contact and About pages. The form itself starts working with the inbox.</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="touch_eyebrow">Small heading</label>
                        <input type="text" class="form-control @error('touch_eyebrow') is-invalid @enderror" id="touch_eyebrow" name="touch_eyebrow" value="{{ $text(old('touch_eyebrow', $getInTouch->eyebrow)) }}" maxlength="60">
                        @error('touch_eyebrow')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label for="touch_title">Title</label>
                        <input type="text" class="form-control @error('touch_title') is-invalid @enderror" id="touch_title" name="touch_title" value="{{ $text(old('touch_title', $getInTouch->title)) }}" required maxlength="120">
                        @error('touch_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <label for="touch_text">Text</label>
                            <span class="char-count" data-for="touch_text"></span>
                        </div>
                        <textarea class="form-control @error('touch_text') is-invalid @enderror" id="touch_text" name="touch_text" rows="3" maxlength="255" data-char-count>{{ $text(old('touch_text', $getInTouch->subtitle)) }}</textarea>
                        @error('touch_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save Contact page'])
        </form>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/contact-page.js') }}"></script>
@endpush
