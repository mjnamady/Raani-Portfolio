@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    {{-- The site's Font Awesome Pro bundle, so icons look exactly like on the public section. --}}
    <link href="{{ asset('frontend/assets/css/vendor/fontawesome.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/skills.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.skills.partials.page_header', [
        'current' => 'My Skill',
        'action' => ['url' => route('admin.skills.create'), 'label' => '+ Add skill'],
    ])

    <div class="row g-4 layout-top-spacing">

        <!-- ===================== SECTION SETTINGS ===================== -->
        <div class="col-xl-4">
            <form method="POST" action="{{ route('admin.skills.settings') }}" novalidate data-track-changes class="sk-settings">
                @csrf
                @method('PUT')

                <div class="sk-card">
                    <h5>Section heading</h5>
                    <p class="sk-subtitle">The small red heading and the large title above the skills.</p>

                    <div class="mb-3">
                        <label for="eyebrow">Small heading</label>
                        <input type="text" class="form-control @error('eyebrow') is-invalid @enderror" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $section->eyebrow) }}" maxlength="60">
                        @error('eyebrow')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="title">Title</label>
                        <textarea class="form-control @error('title') is-invalid @enderror" id="title" name="title" rows="2" required maxlength="120">{{ old('title', $section->title) }}</textarea>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Press Enter where the title should break onto a new line.</div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                        <label class="form-check-label" for="is_active">Show on the home page</label>
                    </div>
                </div>

                @include('admin.layout.components.save_bar', ['label' => 'Save section'])
            </form>
        </div>

        <!-- ===================== SKILL STACK ===================== -->
        <div class="col-xl-8">
            <div class="sk-stack-head">
                <h4>Skills</h4>
                <span>{{ $skills->where('is_active', true)->count() }} of {{ $skills->count() }} shown · ★ marks the row that starts with the red highlight</span>
                <a href="{{ route('admin.skills.create') }}" class="btn btn-primary btn-sm sk-head-add">+ Add skill</a>
            </div>

            <div class="sk-stack">
                @forelse ($skills as $skill)
                    <article class="sk-row {{ $skill->is_highlighted ? 'is-highlighted' : '' }} {{ $skill->is_active ? '' : 'is-hidden-on-site' }}">
                        <span class="sk-icon" aria-hidden="true"><i class="{{ $skill->icon }}"></i></span>

                        <div class="sk-main">
                            <div class="sk-title-line">
                                <h5>{{ $skill->title }}</h5>
                                @if ($skill->is_highlighted)
                                    <span class="sk-badge sk-badge-star">★ Highlighted</span>
                                @endif
                                @unless ($skill->is_active)
                                    <span class="sk-badge sk-badge-hidden">Hidden</span>
                                @endunless
                            </div>
                            <p>{{ Str::limit($skill->description, 140) }}</p>
                        </div>

                        <div class="sk-count">
                            <strong>{{ number_format($skill->done_count) }}</strong>
                            <span>{{ $skill->done_label }}</span>
                        </div>

                        <div class="sk-actions">
                            <form method="POST" action="{{ route('admin.skills.highlight', $skill) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="sk-action {{ $skill->is_highlighted ? 'is-on' : '' }}" title="{{ $skill->is_highlighted ? 'Highlighted' : 'Make this the highlighted skill' }}" aria-label="Highlight {{ $skill->title }}" @disabled($skill->is_highlighted || ! $skill->is_active)>★</button>
                            </form>
                            <form method="POST" action="{{ route('admin.skills.toggle', $skill) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="sk-action" title="{{ $skill->is_active ? 'Hide from the site' : 'Show on the site' }}" aria-label="{{ $skill->is_active ? 'Hide' : 'Show' }} {{ $skill->title }}">
                                    @if ($skill->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.skills.move', $skill) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="sk-action" title="Move up" aria-label="Move {{ $skill->title }} up" @disabled($loop->first)>↑</button>
                            </form>
                            <form method="POST" action="{{ route('admin.skills.move', $skill) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="sk-action" title="Move down" aria-label="Move {{ $skill->title }} down" @disabled($loop->last)>↓</button>
                            </form>
                            <a href="{{ route('admin.skills.edit', $skill) }}" class="sk-action" title="Edit" aria-label="Edit {{ $skill->title }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-confirm="Delete the &quot;{{ $skill->title }}&quot; skill? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sk-action sk-delete" title="Delete" aria-label="Delete {{ $skill->title }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="sk-empty">
                        <p>No skills yet. The My Skill section is hidden on the site until you add one.</p>
                    </div>
                @endforelse

                <a href="{{ route('admin.skills.create') }}" class="sk-add-row">
                    <span class="sk-add-plus" aria-hidden="true">+</span>
                    <span>
                        <strong>{{ $skills->isEmpty() ? 'Add your first skill' : 'Add a skill' }}</strong>
                        <small>Pick an icon, give it a title, count and short description.</small>
                    </span>
                </a>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/skills.js') }}"></script>
@endpush
