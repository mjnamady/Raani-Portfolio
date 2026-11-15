@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/resume-shared.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/resume-education.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.resume.partials.page_header', [
        'current' => $config['label'],
        'action' => ['url' => route('admin.education.create'), 'label' => '+ Add education'],
    ])

    <div class="layout-top-spacing">

        <!-- ===================== SECTION SETTINGS ===================== -->
        <form method="POST" action="{{ route('admin.education.settings') }}" novalidate data-track-changes class="ed-settings">
            @csrf
            @method('PUT')

            <div class="ed-settings-card">
                <div class="ed-settings-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"/><path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4"/></svg>
                </div>

                <div class="ed-settings-field">
                    <label for="title">Section title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $section->title) }}" required maxlength="60">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch ed-settings-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                    <label class="form-check-label" for="is_active">Show on the home page</label>
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save section'])
        </form>

        <!-- ===================== TIMELINE ===================== -->
        <div class="ed-timeline-head">
            <h4>Timeline</h4>
            <span>{{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }} · {{ $entries->where('is_active', true)->count() }} shown · shown on the site as a 2-column grid in this order{{ $entries->where('is_active', true)->count() % 2 ? ' (an even number fills it best)' : '' }}</span>
        </div>

        @if ($entries->isEmpty())
            <div class="ed-empty">
                <p>No education entries yet. The Education block is hidden on the site until you add one.</p>
                <a href="{{ route('admin.education.create') }}" class="btn btn-primary">+ Add your first entry</a>
            </div>
        @else
            <ol class="ed-timeline">
                @foreach ($entries as $entry)
                    <li class="ed-node {{ $entry->is_active ? '' : 'is-hidden-on-site' }}">
                        <span class="ed-dot" aria-hidden="true"></span>
                        <div class="ed-card">
                            <div class="ed-card-main">
                                <span class="ed-period">{{ $entry->period }}</span>
                                <h5>{{ $entry->title }}</h5>
                                @if ($entry->organisation)
                                    <p class="ed-org">{{ $entry->organisation }}</p>
                                @endif
                                <p class="ed-desc">{{ Str::limit($entry->description, 160) }}</p>
                                @unless ($entry->is_active)
                                    <span class="rs-hidden-badge">Hidden on the site</span>
                                @endunless
                            </div>
                            @include('admin.resume.partials.entry_actions', ['first' => $loop->first, 'last' => $loop->last])
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/resume-list.js') }}"></script>
@endpush
