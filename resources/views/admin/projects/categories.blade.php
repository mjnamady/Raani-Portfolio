@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/projects.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.projects.partials.page_header', [
        'trail' => [['Categories', null]],
        'actions' => [['url' => route('admin.projects.index'), 'label' => '← All projects', 'style' => 'light']],
    ])

    <div class="row g-4 layout-top-spacing">

        <!-- ===================== ADD ===================== -->
        <div class="col-xl-4">
            <form method="POST" action="{{ route('admin.project-categories.store') }}" novalidate data-track-changes class="pc-add">
                @csrf
                <h5>Add a category</h5>
                <p>Categories become the filter tabs on the projects page, in the order below.</p>

                <div class="mb-3">
                    <label for="new-name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="new-name" name="name" value="{{ old('name') }}" required maxlength="60" placeholder="e.g. Branding">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-0">
                    <label for="new-slug">URL slug <span class="text-muted">(optional)</span></label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="new-slug" name="slug" value="{{ old('slug') }}" maxlength="80" placeholder="made-from-the-name">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Used in filter links, e.g. <code>/projects?category=branding</code>.</div>
                </div>

                @include('admin.layout.components.save_bar', ['label' => 'Add category'])
            </form>
        </div>

        <!-- ===================== LIST ===================== -->
        <div class="col-xl-8">
            <div class="pc-list">
                @forelse ($categories as $category)
                    <div class="pc-row">
                        <span class="pc-position">{{ $loop->iteration }}</span>

                        <form method="POST" action="{{ route('admin.project-categories.update', $category) }}" class="pc-edit">
                            @csrf
                            @method('PUT')
                            <label for="name-{{ $category->id }}" class="visually-hidden">Name</label>
                            <input type="text" class="form-control form-control-sm" id="name-{{ $category->id }}" name="name" value="{{ $category->name }}" required maxlength="60">
                            <label for="slug-{{ $category->id }}" class="visually-hidden">URL slug</label>
                            <input type="text" class="form-control form-control-sm pc-slug" id="slug-{{ $category->id }}" name="slug" value="{{ $category->slug }}" maxlength="80">
                            <button type="submit" class="btn btn-light-primary btn-sm">Save</button>
                        </form>

                        <span class="pc-count">{{ $category->projects_count }} {{ Str::plural('project', $category->projects_count) }}</span>

                        <div class="pc-actions">
                            <form method="POST" action="{{ route('admin.project-categories.move', $category) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="pj-icon-btn" aria-label="Move {{ $category->name }} up" title="Move up" @disabled($loop->first)>↑</button>
                            </form>
                            <form method="POST" action="{{ route('admin.project-categories.move', $category) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="pj-icon-btn" aria-label="Move {{ $category->name }} down" title="Move down" @disabled($loop->last)>↓</button>
                            </form>
                            <form method="POST" action="{{ route('admin.project-categories.destroy', $category) }}"
                                  data-confirm="Delete the &quot;{{ $category->name }}&quot; category?{{ $category->projects_count ? ' Its '.$category->projects_count.' project(s) will become uncategorised.' : '' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pj-icon-btn pj-delete" aria-label="Delete {{ $category->name }}" title="Delete">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="pj-empty mb-0">
                        <p class="mb-0">No categories yet. Projects can stay uncategorised; the "All" tab always shows them.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/projects.js') }}"></script>
@endpush
