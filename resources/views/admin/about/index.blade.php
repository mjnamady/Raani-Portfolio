@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/about-page.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $editedBar = (string) old('bar_id');
@endphp

<div class="middle-content container-xxl p-0">

    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="About Page">
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
                                <li class="breadcrumb-item active" aria-current="page">About Page</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto gap-2">
                    <li class="nav-item">
                        <a href="{{ route('about.index') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">View About page</a>
                    </li>
                </ul>
            </header>
        </div>
    </div>

    <div class="layout-top-spacing">

        <!-- ===================== ALSO ON THIS PAGE ===================== -->
        <div class="ab-reused">
            <span class="ab-reused-label">Also on the About page, edited on their own pages:</span>
            <a href="{{ route('admin.services') }}">Services Area</a>
            <a href="{{ route('admin.counter') }}">Counter Area</a>
            <a href="{{ route('admin.education.index') }}">Education</a>
            <a href="{{ route('admin.experience.index') }}">Experience</a>
            <a href="{{ route('admin.testimonials.index') }}">Testimonials</a>
            <a href="{{ route('admin.contact.index') }}">Get in touch (Contact Page)</a>
        </div>

        <!-- ===================== SETTINGS: COLUMN TITLES ===================== -->
        <form method="POST" action="{{ route('admin.about.settings') }}" novalidate data-track-changes class="ab-settings">
            @csrf
            @method('PUT')

            <div class="ab-card">
                <h5>Skill columns</h5>
                <p class="ab-subtitle">The two headings above the progress bars. A column switched off, or with no visible bars, is hidden.</p>

                <div class="row g-4">
                    @foreach ($columns as $column)
                        <div class="col-xl-6">
                            <div class="ab-column-setting">
                                <div class="flex-grow-1">
                                    <label for="{{ $column['key'] }}_title">{{ ucfirst($column['key']) }} column title</label>
                                    <input type="text" class="form-control @error($column['key'].'_title') is-invalid @enderror" id="{{ $column['key'] }}_title" name="{{ $column['key'] }}_title" value="{{ old($column['key'].'_title', $column['section']->title) }}" required maxlength="60">
                                    @error($column['key'].'_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="hidden" name="{{ $column['key'] }}_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="{{ $column['key'] }}_active" name="{{ $column['key'] }}_active" value="1" @checked(old($column['key'].'_active', $column['section']->is_active))>
                                    <label class="form-check-label" for="{{ $column['key'] }}_active">Show</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save About page'])
        </form>

        <!-- ===================== SKILL BOARD ===================== -->
        <div class="row g-4 mt-1">
            @foreach ($columns as $column)
                @php
                    $bag = 'addBar'.ucfirst($column['key']);
                @endphp
                <div class="col-xl-6">
                    <section class="ab-board" aria-labelledby="board-{{ $column['key'] }}">
                        <header class="ab-board-head">
                            <h4 id="board-{{ $column['key'] }}">{{ $column['section']->title }}</h4>
                            <span>{{ $column['bars']->where('is_active', true)->count() }} of {{ $column['bars']->count() }} shown</span>
                            @unless ($column['section']->is_active)
                                <span class="ab-badge-off">Column hidden</span>
                            @endunless
                        </header>

                        @forelse ($column['bars'] as $bar)
                            @php
                                $isEdited = $editedBar === (string) $bar->id;
                                $percent = $isEdited ? old('percent', $bar->percent) : $bar->percent;
                            @endphp
                            <div class="ab-bar {{ $bar->is_active ? '' : 'is-hidden-on-site' }}">
                                <form method="POST" action="{{ route('admin.about.bars.update', $bar) }}" class="ab-bar-edit" novalidate>
                                    @csrf
                                    @method('PUT')
                                    {{-- Only tells the page which row to show errors on; never used to pick the record. --}}
                                    <input type="hidden" name="bar_id" value="{{ $bar->id }}">
                                    <label for="bar-name-{{ $bar->id }}" class="visually-hidden">Skill name</label>
                                    <input type="text" class="form-control form-control-sm ab-bar-name @if ($isEdited) @error('name', 'updateBar') is-invalid @enderror @endif" id="bar-name-{{ $bar->id }}" name="name" value="{{ $isEdited ? old('name', $bar->name) : $bar->name }}" required maxlength="60">
                                    <label for="bar-percent-{{ $bar->id }}" class="visually-hidden">Percentage for {{ $bar->name }}</label>
                                    <div class="ab-percent">
                                        <input type="number" class="form-control form-control-sm @if ($isEdited) @error('percent', 'updateBar') is-invalid @enderror @endif" id="bar-percent-{{ $bar->id }}" name="percent" value="{{ $percent }}" min="0" max="100" step="1" required data-bar-percent>
                                        <span aria-hidden="true">%</span>
                                    </div>
                                    <button type="submit" class="btn btn-light-primary btn-sm">Save</button>
                                    <div class="ab-meter" aria-hidden="true"><span style="width: {{ max(0, min(100, (int) $percent)) }}%"></span></div>
                                    @if ($isEdited)
                                        @foreach ($errors->updateBar->all() as $message)
                                            <div class="ab-row-error">{{ $message }}</div>
                                        @endforeach
                                    @endif
                                </form>

                                <div class="ab-bar-actions">
                                    @unless ($bar->is_active)
                                        <span class="ab-badge-hidden">Hidden</span>
                                    @endunless
                                    <form method="POST" action="{{ route('admin.about.bars.toggle', $bar) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="ab-icon-btn" title="{{ $bar->is_active ? 'Hide from the site' : 'Show on the site' }}" aria-label="{{ $bar->is_active ? 'Hide' : 'Show' }} {{ $bar->name }}">
                                            @if ($bar->is_active)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.about.bars.move', $bar) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="ab-icon-btn" title="Move up" aria-label="Move {{ $bar->name }} up" @disabled($loop->first)>↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.about.bars.move', $bar) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="ab-icon-btn" title="Move down" aria-label="Move {{ $bar->name }} down" @disabled($loop->last)>↓</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.about.bars.destroy', $bar) }}" data-confirm="Delete the skill &quot;{{ $bar->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ab-icon-btn ab-delete" title="Delete" aria-label="Delete {{ $bar->name }}">✕</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="ab-empty">No skills in this column yet; it stays hidden on the site until you add one.</p>
                        @endforelse

                        <!-- Add a bar to this column -->
                        <form method="POST" action="{{ route('admin.about.bars.store') }}" class="ab-add" novalidate>
                            @csrf
                            <input type="hidden" name="group" value="{{ $column['key'] }}">
                            <span class="ab-add-plus" aria-hidden="true">+</span>
                            <label for="add-name-{{ $column['key'] }}" class="visually-hidden">New skill name</label>
                            <input type="text" class="form-control form-control-sm @error('name', $bag) is-invalid @enderror" id="add-name-{{ $column['key'] }}" name="name" value="{{ $errors->$bag->any() ? old('name') : '' }}" placeholder="Add a skill, e.g. Figma" maxlength="60" required>
                            <label for="add-percent-{{ $column['key'] }}" class="visually-hidden">New skill percentage</label>
                            <div class="ab-percent">
                                <input type="number" class="form-control form-control-sm @error('percent', $bag) is-invalid @enderror" id="add-percent-{{ $column['key'] }}" name="percent" value="{{ $errors->$bag->any() ? old('percent') : '' }}" placeholder="80" min="0" max="100" step="1" required>
                                <span aria-hidden="true">%</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Add skill</button>
                            @foreach ($errors->$bag->all() as $message)
                                <div class="ab-row-error">{{ $message }}</div>
                            @endforeach
                        </form>
                    </section>
                </div>
            @endforeach
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/about-page.js') }}"></script>
@endpush
