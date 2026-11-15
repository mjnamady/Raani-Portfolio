{{--
    Breadcrumb bar for the blog pages.
    $trail    list of [label, url|null] after "Blog"; the last one is the current page
    $actions  optional list of ['url' => ..., 'label' => ..., 'style' => 'primary'|'light'] buttons
--}}
<div class="secondary-nav">
    <div class="breadcrumbs-container" data-page-heading="Blog">
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
                            <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">Blog</a></li>
                            @foreach ($trail as [$label, $url])
                                @if ($loop->last || ! $url)
                                    <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                                @else
                                    <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>

                </div>
            </div>
            <ul class="navbar-nav flex-row ms-auto gap-2">
                @foreach ($actions ?? [] as $action)
                    <li class="nav-item">
                        <a href="{{ $action['url'] }}" class="btn btn-{{ ($action['style'] ?? 'primary') === 'light' ? 'light-primary' : 'primary' }} btn-sm" @if (! empty($action['external'])) target="_blank" rel="noopener" @endif>{{ $action['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </header>
    </div>
</div>
