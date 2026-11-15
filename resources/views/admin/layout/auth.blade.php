<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Sign in') · {{ config('app.name') }} Admin</title>

    {{-- App-name initials as the tab icon, in place of Laravel's default favicon.ico. --}}
    @php
        $authInitials = collect(preg_split('/[\s\-_]+/', (string) config('app.name'), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('') ?: 'A';
        $authFavicon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4361ee"/><stop offset="1" stop-color="#805dca"/></linearGradient></defs>'
            .'<rect width="64" height="64" rx="14" fill="url(#g)"/>'
            .'<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Montserrat,Arial,sans-serif" font-size="'.(mb_strlen($authInitials) > 1 ? 26 : 34).'" font-weight="700" fill="#fff">'.e($authInitials).'</text>'
            .'</svg>';
    @endphp
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode($authFavicon) }}">

    {{-- Apply the admin's saved colour mode before paint (same "adminTheme" key as the admin panel): dark unless switched to light. --}}
    <script>
        (function () {
            var dark = true;
            try { dark = localStorage.getItem('adminTheme') !== 'light'; } catch (e) {}
            if (dark) {
                document.documentElement.classList.add('auth-dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('backend/src/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/layouts/modern-dark-menu/css/light/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/layouts/modern-dark-menu/css/dark/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/custom/css/admin-auth.css') }}" rel="stylesheet" type="text/css" />
    @stack('styles')
</head>
<body class="auth-page dark">

    <script>
        document.body.classList.toggle('dark', document.documentElement.classList.contains('auth-dark'));
    </script>

    <button type="button" class="auth-theme-toggle" id="auth-theme-toggle" aria-label="Switch between light and dark mode">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-moon" aria-hidden="true"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-sun" aria-hidden="true"><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"/></svg>
    </button>

    <main class="auth-wrapper">
        <div class="auth-card">

            <!-- Brand panel -->
            <aside class="auth-brand" aria-hidden="true">
                <div class="auth-brand-inner">
                    <div>
                        <p class="auth-wordmark">{{ config('app.name') }}</p>
                        <span class="auth-wordmark-tag">Admin panel</span>
                    </div>

                    <div class="auth-brand-copy">
                        <h2>@yield('brand_heading', 'Manage your portfolio')</h2>
                        <p>@yield('brand_text', 'Update your profile, projects, blog and messages from one private place.')</p>
                    </div>

                    <ul class="auth-brand-points">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/><path d="M9 12l2 2l4 -4"/></svg>
                            Private, owner-only access
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h6v6h-6z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6h-6z"/><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/></svg>
                            Every section editable without code
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11"/></svg>
                            Changes go live instantly
                        </li>
                    </ul>
                </div>
                <span class="auth-blob auth-blob-1"></span>
                <span class="auth-blob auth-blob-2"></span>
            </aside>

            <!-- Form panel -->
            <section class="auth-form-panel">
                @yield('content')

                <p class="auth-footer">
                    &copy; {{ now()->year }} {{ config('app.name') }}.
                    <a href="{{ url('/') }}">Back to website</a>
                </p>
            </section>

        </div>
    </main>

    <script src="{{ asset('backend/custom/js/admin-auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
