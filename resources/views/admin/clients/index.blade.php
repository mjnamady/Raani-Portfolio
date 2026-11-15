@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/clients.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

<div class="middle-content container-xxl p-0">

    @include('admin.clients.partials.page_header', [
        'current' => 'Supported Companies',
        'action' => ['url' => route('admin.clients.create'), 'label' => '+ Add logo'],
    ])

    <div class="layout-top-spacing">

        <!-- ===================== TOP BAR: VISIBILITY ===================== -->
        <form method="POST" action="{{ route('admin.clients.settings') }}" novalidate data-track-changes class="cl-settings">
            @csrf
            @method('PUT')

            <div class="cl-topbar">
                <div>
                    <h4>Logo wall</h4>
                    <p>
                        {{ $clients->count() }} {{ Str::plural('logo', $clients->count()) }} · {{ $clients->where('is_active', true)->count() }} shown ·
                        4 per row on desktop, in this order. Light or white logos on a transparent background look best on the dark site.
                    </p>
                </div>
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $section->is_active))>
                    <label class="form-check-label" for="is_active">Show the section on the home page</label>
                </div>
            </div>

            @include('admin.layout.components.save_bar', ['label' => 'Save section'])
        </form>

        <!-- ===================== LOGO WALL ===================== -->
        <div class="cl-wall">
            @foreach ($clients as $client)
                <div class="cl-tile {{ $client->is_active ? '' : 'is-hidden-on-site' }}">
                    <div class="cl-logo">
                        <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" loading="lazy">
                    </div>

                    <div class="cl-meta">
                        <span class="cl-name">{{ $client->name }}</span>
                        @if ($client->url)
                            <span class="cl-link" title="{{ $client->url }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/></svg>
                                <span class="visually-hidden">Links to {{ $client->url }}</span>
                            </span>
                        @endif
                        @unless ($client->is_active)
                            <span class="cl-hidden-badge">Hidden</span>
                        @endunless
                    </div>

                    <div class="cl-actions">
                        <form method="POST" action="{{ route('admin.clients.toggle', $client) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="cl-action" title="{{ $client->is_active ? 'Hide from the site' : 'Show on the site' }}" aria-label="{{ $client->is_active ? 'Hide' : 'Show' }} {{ $client->name }}">
                                @if ($client->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                                @endif
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.clients.move', $client) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="left">
                            <button type="submit" class="cl-action" title="Move earlier" aria-label="Move {{ $client->name }} earlier" @disabled($loop->first)>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6l6 6"/></svg>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.clients.move', $client) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="right">
                            <button type="submit" class="cl-action" title="Move later" aria-label="Move {{ $client->name }} later" @disabled($loop->last)>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6l-6 6"/></svg>
                            </button>
                        </form>

                        <a href="{{ route('admin.clients.edit', $client) }}" class="cl-action" title="Edit" aria-label="Edit {{ $client->name }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/><path d="M13.5 6.5l4 4"/></svg>
                        </a>

                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" data-confirm="Delete the &quot;{{ $client->name }}&quot; logo? This cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cl-action cl-delete" title="Delete" aria-label="Delete {{ $client->name }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('admin.clients.create') }}" class="cl-tile cl-add">
                <span class="cl-add-plus" aria-hidden="true">+</span>
                <strong>Add logo</strong>
                @if ($clients->isEmpty())
                    <small>No logos yet: the section is hidden until you add one.</small>
                @endif
            </a>
        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/clients.js') }}"></script>
@endpush
