@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/projects.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.projects.partials.page_header', [
        'trail' => [['Projects', null]],
        'actions' => [
            ['url' => route('admin.project-categories.index'), 'label' => 'Categories', 'style' => 'light'],
            ['url' => route('admin.projects.create'), 'label' => '+ New project'],
        ],
    ])

    <div class="layout-top-spacing">

        <!-- ===================== TOOLBAR: FILTERS + HOME SECTION ===================== -->
        <div class="pj-toolbar">
            <nav class="pj-filters" aria-label="Filter by status">
                <a href="{{ route('admin.projects.index', array_filter(['category' => $filters['category']])) }}" class="{{ $filters['status'] === null ? 'is-active' : '' }}">All <span>{{ $counts['all'] }}</span></a>
                <a href="{{ route('admin.projects.index', array_filter(['status' => 'published', 'category' => $filters['category']])) }}" class="{{ $filters['status'] === 'published' ? 'is-active' : '' }}">Published <span>{{ $counts['published'] }}</span></a>
                <a href="{{ route('admin.projects.index', array_filter(['status' => 'draft', 'category' => $filters['category']])) }}" class="{{ $filters['status'] === 'draft' ? 'is-active' : '' }}">Drafts <span>{{ $counts['draft'] }}</span></a>
            </nav>

            <form method="GET" action="{{ route('admin.projects.index') }}" class="pj-category-filter" data-auto-submit>
                @if ($filters['status'])
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                @endif
                <label for="filter-category" class="visually-hidden">Filter by category</label>
                <select id="filter-category" name="category" class="form-select form-select-sm">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn btn-light-primary btn-sm">Filter</button></noscript>
            </form>
        </div>

        <form method="POST" action="{{ route('admin.projects.settings') }}" novalidate data-track-changes class="pj-home-settings">
            @csrf
            @method('PUT')

            <div class="pj-home-card">
                <div>
                    <strong>Home page "Latest Portfolio"</strong>
                    <span>Shows up to {{ \App\Models\Project::HOME_LIMIT }} featured (★) published projects in this order, or the latest published ones when none are featured.</span>
                </div>
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                    <label class="form-check-label" for="is_active">Show on the home page</label>
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save section'])
        </form>

        <!-- ===================== BOARD ===================== -->
        <div class="pj-board">
            <a href="{{ route('admin.projects.create') }}" class="pj-card pj-add-card">
                <span class="pj-add-plus" aria-hidden="true">+</span>
                <strong>New project</strong>
                <small>
                    @if ($counts['all'] === 0)
                        No projects yet. Start with your best work.
                    @elseif ($projects->isEmpty())
                        No projects match this filter.
                    @else
                        Add a project, then publish it when it is ready.
                    @endif
                </small>
            </a>

                @foreach ($projects as $project)
                    <article class="pj-card {{ $project->isPublished() ? '' : 'is-draft' }}">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="pj-thumb" tabindex="-1" aria-hidden="true">
                            @if ($project->card_image_url)
                                <img src="{{ $project->card_image_url }}" alt="" loading="lazy">
                            @else
                                <span class="pj-thumb-empty">No card image</span>
                            @endif
                            <span class="pj-status {{ $project->isPublished() ? 'is-published' : 'is-draft' }}">{{ $project->isPublished() ? 'Published' : 'Draft' }}</span>
                            @if ($project->is_featured)
                                <span class="pj-star" title="Featured on the home page">★</span>
                            @endif
                        </a>

                        <div class="pj-body">
                            <span class="pj-category">{{ $project->category?->name ?? 'Uncategorised' }}</span>
                            <h5><a href="{{ route('admin.projects.edit', $project) }}">{{ $project->title }}</a></h5>
                            @if ($project->tagline)
                                <p class="pj-tagline">{{ $project->tagline }}</p>
                            @endif
                            <p class="pj-meta">
                                {{ $project->images_count }} gallery {{ Str::plural('image', $project->images_count) }}
                                @if ($project->published_at)
                                    · {{ $project->published_at->format('d M Y') }}
                                @endif
                            </p>
                        </div>

                        <div class="pj-actions">
                            <form method="POST" action="{{ route('admin.projects.publish', $project) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="pj-action pj-publish" title="{{ $project->isPublished() ? 'Unpublish (back to draft)' : 'Publish' }}">
                                    {{ $project->isPublished() ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.projects.feature', $project) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="pj-action {{ $project->is_featured ? 'is-on' : '' }}" title="{{ $project->is_featured ? 'Remove from the home page' : 'Feature on the home page' }}" aria-label="{{ $project->is_featured ? 'Unfeature' : 'Feature' }} {{ $project->title }}">★</button>
                            </form>
                            <form method="POST" action="{{ route('admin.projects.move', $project) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="pj-action" title="Move earlier" aria-label="Move {{ $project->title }} earlier" @disabled($loop->first)>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6l6 6"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.projects.move', $project) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="pj-action" title="Move later" aria-label="Move {{ $project->title }} later" @disabled($loop->last)>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6l-6 6"/></svg>
                                </button>
                            </form>
                            @if ($project->isPublished())
                                <a href="{{ route('projects.show', $project->slug) }}" class="pj-action" target="_blank" rel="noopener" title="View on the site" aria-label="View {{ $project->title }} on the site">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6"/><path d="M11 13l9 -9"/><path d="M15 4h5v5"/></svg>
                                </a>
                            @endif
                            <a href="{{ route('admin.projects.edit', $project) }}" class="pj-action" title="Edit" aria-label="Edit {{ $project->title }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" data-confirm="Delete &quot;{{ $project->title }}&quot; and its gallery? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pj-action pj-delete" title="Delete" aria-label="Delete {{ $project->title }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/projects.js') }}"></script>
@endpush
