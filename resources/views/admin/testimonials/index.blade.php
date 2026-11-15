@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/testimonials.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.testimonials.partials.page_header', [
        'current' => 'Testimonials',
        'action' => ['url' => route('admin.testimonials.create'), 'label' => '+ Add testimonial'],
    ])

    <!-- ===================== SECTION SETTINGS ===================== -->
    <form method="POST" action="{{ route('admin.testimonials.settings') }}" novalidate data-track-changes class="layout-top-spacing">
        @csrf
        @method('PUT')

        <div class="tm-settings">
            <div class="tm-settings-preview" aria-hidden="true">
                <span id="tm-title-preview">{{ old('title', $section->title) ?: 'Testimonial' }}</span>
            </div>
            <div class="tm-settings-fields">
                <h5>Section title</h5>
                <p class="tm-subtitle">The huge outlined word above the slider. Keep it to a word or two.</p>

                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label for="title">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $section->title) }}" required maxlength="40" data-title-preview>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                            <label class="form-check-label" for="is_active">Show on the home page</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.layout.components.save_bar', ['label' => 'Save section'])
    </form>

    <!-- ===================== QUOTE WALL ===================== -->
    <div class="tm-wall-head">
        <h4>Testimonials</h4>
        <span>{{ $testimonials->where('is_active', true)->count() }} of {{ $testimonials->count() }} shown · the slider shows 2 at a time (1 on phones)</span>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm tm-head-add">+ Add testimonial</a>
    </div>

    <div class="tm-wall">
        @foreach ($testimonials as $testimonial)
            <article class="tm-card {{ $testimonial->is_active ? '' : 'is-hidden-on-site' }}">
                <div class="tm-card-top">
                    <span class="tm-quote-mark" aria-hidden="true">&ldquo;</span>
                    <span class="tm-position">#{{ $loop->iteration }}</span>
                    @unless ($testimonial->is_active)
                        <span class="tm-badge-hidden">Hidden</span>
                    @endunless
                </div>

                <blockquote class="tm-quote">{{ $testimonial->quote }}</blockquote>

                <div class="tm-card-foot">
                    <div class="tm-person">
                        <strong>{{ $testimonial->name }}</strong>
                        @if (filled($testimonial->role))
                            <span>{{ $testimonial->role }}</span>
                        @endif
                    </div>

                    <div class="tm-actions">
                        <form method="POST" action="{{ route('admin.testimonials.toggle', $testimonial) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="tm-action" title="{{ $testimonial->is_active ? 'Hide from the site' : 'Show on the site' }}" aria-label="{{ $testimonial->is_active ? 'Hide' : 'Show' }} the testimonial from {{ $testimonial->name }}">
                                @if ($testimonial->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                                @endif
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.testimonials.move', $testimonial) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="tm-action" title="Move earlier" aria-label="Move the testimonial from {{ $testimonial->name }} earlier" @disabled($loop->first)>←</button>
                        </form>
                        <form method="POST" action="{{ route('admin.testimonials.move', $testimonial) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="tm-action" title="Move later" aria-label="Move the testimonial from {{ $testimonial->name }} later" @disabled($loop->last)>→</button>
                        </form>
                        <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="tm-action" title="Edit" aria-label="Edit the testimonial from {{ $testimonial->name }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" data-confirm="Delete the testimonial from &quot;{{ $testimonial->name }}&quot;? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tm-action tm-delete" title="Delete" aria-label="Delete the testimonial from {{ $testimonial->name }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach

        <a href="{{ route('admin.testimonials.create') }}" class="tm-card tm-add-card">
            <span class="tm-add-plus" aria-hidden="true">+</span>
            <strong>{{ $testimonials->isEmpty() ? 'Add your first testimonial' : 'Add testimonial' }}</strong>
            <small>
                @if ($testimonials->isEmpty())
                    The Testimonial section is hidden on the site until you add one.
                @else
                    A quote, the person's name and their role.
                @endif
            </small>
        </a>
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/testimonials.js') }}"></script>
@endpush
