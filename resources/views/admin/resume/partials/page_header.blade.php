{{--
    Breadcrumb bar for the education and experience pages.
    $config  the controller config
    $current the last breadcrumb label
    $action  optional ['url' => ..., 'label' => ...] primary button
--}}
<div class="secondary-nav">
    <div class="breadcrumbs-container" data-page-heading="{{ $config['label'] }}">
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
                            @if ($current === $config['label'])
                                <li class="breadcrumb-item active" aria-current="page">{{ $config['label'] }}</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route($config['route'].'.index') }}">{{ $config['label'] }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>
                            @endif
                        </ol>
                    </nav>

                </div>
            </div>
            <ul class="navbar-nav flex-row ms-auto gap-2">
                <li class="nav-item">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                        View on site
                    </a>
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
