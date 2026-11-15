@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/counter-area.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $isShown = (bool) old('is_active', $section->is_active);
@endphp

<div class="middle-content container-xxl p-0">

    <!--  BEGIN BREADCRUMBS  -->
    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Counter Area">
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
                                <li class="breadcrumb-item active" aria-current="page">Counter Area</li>
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

    <form method="POST" action="{{ route('admin.counter.update') }}" novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @method('PUT')

        <!-- ===================== TOOLBAR ===================== -->
        <div class="ct-toolbar">
            <div>
                <h4>Scoreboard</h4>
                <p>Type straight into the tiles: they are laid out like the section on the home page.</p>
            </div>

            <fieldset class="ct-segmented" aria-label="Show the counter area on the home page">
                <input type="radio" class="visually-hidden" name="is_active" id="is_active" value="1" @checked($isShown)>
                <label for="is_active">
                    <span class="ct-seg-dot" aria-hidden="true"></span> Shown
                </label>
                <input type="radio" class="visually-hidden" name="is_active" id="is_active_off" value="0" @checked(! $isShown)>
                <label for="is_active_off">Hidden</label>
            </fieldset>
        </div>

        @error('counters')
            <div class="alert alert-light-danger mb-3">{{ $message }}</div>
        @enderror

        <div class="ct-board">

            <!-- ===================== HIGHLIGHT TILE ===================== -->
            <div class="ct-highlight">
                <span class="ct-tile-tag">Highlight</span>

                <div class="ct-big-number">
                    <label for="highlight_value" class="visually-hidden">Highlight number</label>
                    <input type="number" class="ct-number-input @error('highlight.value') is-invalid @enderror" id="highlight_value" name="highlight[value]" value="{{ old('highlight.value', $highlight?->value) }}" required min="0" max="9999999" step="1" placeholder="0" data-autowidth>
                    <label for="highlight_suffix" class="visually-hidden">Highlight suffix</label>
                    <input type="text" class="ct-suffix-input @error('highlight.suffix') is-invalid @enderror" id="highlight_suffix" name="highlight[suffix]" value="{{ old('highlight.suffix', $highlight?->suffix) }}" maxlength="5" placeholder="+">
                </div>
                @error('highlight.value')
                    <div class="ct-error">{{ $message }}</div>
                @enderror
                @error('highlight.suffix')
                    <div class="ct-error">{{ $message }}</div>
                @enderror

                <label for="highlight_label" class="ct-field-label">Label <span class="ct-hint">Enter = line break</span></label>
                <textarea class="ct-label-input ct-label-big @error('highlight.label') is-invalid @enderror" id="highlight_label" name="highlight[label]" rows="2" required maxlength="60" placeholder="Years of&#10;experience" data-autosize>{{ old('highlight.label', $highlight?->label) }}</textarea>
                @error('highlight.label')
                    <div class="ct-error">{{ $message }}</div>
                @enderror

                <label for="highlight_description" class="ct-field-label">Description <span class="ct-hint" data-count-for="highlight_description"></span></label>
                <textarea class="ct-label-input ct-description @error('highlight.description') is-invalid @enderror" id="highlight_description" name="highlight[description]" rows="3" maxlength="300" placeholder="Optional paragraph under the highlight" data-autosize data-count>{{ old('highlight.description', $highlight?->description) }}</textarea>
                @error('highlight.description')
                    <div class="ct-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- ===================== STAT TILES ===================== -->
            <div class="ct-grid">
                @foreach ($counterRows as $i => $row)
                    <div class="ct-tile" data-counter-tile>
                        <div class="ct-tile-head">
                            <span class="ct-tile-tag">Counter {{ $i + 1 }}</span>
                            <button type="button" class="ct-clear" data-clear aria-label="Clear counter {{ $i + 1 }}" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="ct-number">
                            <label for="counters_{{ $i }}_value" class="visually-hidden">Counter {{ $i + 1 }} number</label>
                            <input type="number" class="ct-number-input @error("counters.$i.value") is-invalid @enderror" id="counters_{{ $i }}_value" name="counters[{{ $i }}][value]" value="{{ old("counters.$i.value", $row?->value) }}" min="0" max="9999999" step="1" placeholder="0" data-counter="value" data-autowidth>
                            <label for="counters_{{ $i }}_suffix" class="visually-hidden">Counter {{ $i + 1 }} suffix</label>
                            <input type="text" class="ct-suffix-input @error("counters.$i.suffix") is-invalid @enderror" id="counters_{{ $i }}_suffix" name="counters[{{ $i }}][suffix]" value="{{ old("counters.$i.suffix", $row?->suffix) }}" maxlength="5" placeholder="k+" data-counter="suffix">
                        </div>

                        <label for="counters_{{ $i }}_label" class="visually-hidden">Counter {{ $i + 1 }} label</label>
                        <input type="text" class="ct-label-input @error("counters.$i.label") is-invalid @enderror" id="counters_{{ $i }}_label" name="counters[{{ $i }}][label]" value="{{ old("counters.$i.label", $row?->label) }}" maxlength="60" placeholder="Label, e.g. Projects completed" data-counter="label">

                        @foreach (['value', 'suffix', 'label'] as $part)
                            @error("counters.$i.$part")
                                <div class="ct-error">{{ $message }}</div>
                            @enderror
                        @endforeach

                        <span class="ct-empty-note">Empty: not shown on the site</span>
                    </div>
                @endforeach
            </div>

        </div>

        @include('admin.layout.components.save_bar', ['label' => 'Save counter area'])
    </form>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/counter-area.js') }}"></script>
@endpush
