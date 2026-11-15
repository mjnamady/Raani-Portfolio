{{--
    Breadcrumb bar for the Testimonials pages.
    $current  the last breadcrumb label ("Testimonials" on the list page)
    $action   optional ['url' => ..., 'label' => ...] primary button
--}}
<div class="secondary-nav">
    <div class="breadcrumbs-container" data-page-heading="Testimonials">
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
                            @if ($current === 'Testimonials')
                                <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>
                            @endif
                        </ol>
                    </nav>

                </div>
            </div>
            <ul class="navbar-nav flex-row ms-auto gap-2">
                <li class="nav-item">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">View on site</a>
                </li>
                @isset($action)
                    <li class="nav-item">
                        <a href="{{ $action['url'] }}" class="btn btn-primary btn-sm">{{ $action['label'] }}</a>
                    </li>
                @endisset
            </ul>
        </header>
    </div>
</div>
