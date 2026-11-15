@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    {{-- The site's Font Awesome Pro bundle, so icon previews match the public cards exactly. --}}
    <link href="{{ asset('frontend/assets/css/vendor/fontawesome.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/services-area.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    // After a failed save, re-show exactly what was submitted (including new and reordered rows).
    $oldRows = old('services');
    $rows = is_array($oldRows) ? $oldRows : $services->map(fn ($service) => [
        'id' => $service->id,
        'title' => $service->title,
        'icon' => $service->icon,
        'project_count' => $service->project_count,
        'project_count_label' => $service->project_count_label,
        'is_active' => $service->is_active,
    ])->all();
@endphp

<div class="middle-content container-xxl p-0">

    <!--  BEGIN BREADCRUMBS  -->
    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Services Area">
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
                                <li class="breadcrumb-item active" aria-current="page">Services Area</li>
                            </ol>
                        </nav>

                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto">
                    <li class="nav-item">
                        <a href="{{ url('/') }}#services" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                            View on site
                        </a>
                    </li>
                </ul>
            </header>
        </div>
    </div>
    <!--  END BREADCRUMBS  -->

    <div class="row layout-top-spacing">
        <div class="col-12">

            <form method="POST" action="{{ route('admin.services.update') }}" id="services-form" novalidate data-max="{{ $maxServices }}" data-track-changes>
                @csrf
                @method('PUT')
                {{-- Always sent, so removing every card is an explicit choice; card fields below override it. --}}
                <input type="hidden" name="services" value="">

                <!-- Board header -->
                <div class="sv-board-head">
                    <div>
                        <h4>Service cards</h4>
                        <p>The cards under the banner on the home page, in this order. 4 fill one row on desktop.</p>
                    </div>
                    <div class="sv-board-meter" aria-live="polite">
                        <span class="sv-board-count"><strong id="sv-count">{{ count($rows) }}</strong> / {{ $maxServices }}</span>
                        <span class="sv-board-shown"><span id="sv-shown">0</span> shown on site</span>
                    </div>
                </div>

                @error('services')
                    <div class="alert alert-light-danger mb-3">{{ $message }}</div>
                @enderror

                <!-- Board -->
                <div class="sv-board" id="service-rows">
                    @foreach ($rows as $key => $row)
                        @include('admin.pages.partials.service_row', ['key' => $key, 'row' => $row])
                    @endforeach

                    <div class="sv-tile-col sv-add-col" id="sv-add-col">
                        <button type="button" class="sv-add-tile" id="add-service">
                            <span class="sv-add-plus" aria-hidden="true">+</span>
                            <strong>Add service</strong>
                            <small id="services-empty" class="{{ count($rows) ? 'd-none' : '' }}">No cards yet: the section is hidden until you add one.</small>
                        </button>
                    </div>
                </div>

                <p class="sv-board-help">
                    Icons use the site's Font Awesome Pro set: a style (<code>fa-light</code>, <code>fa-regular</code>, <code>fa-solid</code>, <code>fa-thin</code>, <code>fa-duotone</code>, <code>fa-brands</code>) and an icon name, e.g. <code>fa-light fa-pen-ruler</code>.
                    The eye switches a card off but keeps it here. Removing a card takes it off the home page when you save; the service itself is kept.
                </p>

                @include('admin.layout.components.save_bar', ['label' => 'Save services'])
            </form>

            <datalist id="icon-suggestions">
                @foreach ($iconSuggestions as $suggestion)
                    <option value="fa-light fa-{{ $suggestion }}"></option>
                @endforeach
            </datalist>

            <template id="service-row-template">
                @include('admin.pages.partials.service_row', ['key' => '__KEY__', 'row' => [
                    'title' => '',
                    'icon' => 'fa-light fa-pen-ruler',
                    'project_count' => 0,
                    'project_count_label' => 'Projects',
                    'is_active' => true,
                ]])
            </template>

        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/services-area.js') }}"></script>
@endpush
