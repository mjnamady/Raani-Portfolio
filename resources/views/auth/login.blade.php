@extends('admin.layout.auth')

@section('title', 'Sign in')

@section('content')

    <div class="auth-form-header">
        <h1>Welcome back</h1>
        <p>Sign in to the admin panel with your email or username.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-light-success auth-alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    @error('login')
        <div class="alert alert-light-danger auth-alert" role="alert">
            {{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('login') }}" class="auth-form" id="login-form" novalidate>
        @csrf

        <!-- Email or Username -->
        <div class="auth-field">
            <label for="login">Email or username</label>
            <div class="auth-input @error('login') is-invalid @enderror">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                </span>
                <input id="login"
                       type="text"
                       name="login"
                       value="{{ old('login') }}"
                       class="auth-input-control"
                       placeholder="you@example.com or username"
                       required
                       autofocus
                       maxlength="255"
                       autocomplete="username"
                       autocapitalize="none"
                       spellcheck="false"
                       @error('login') aria-invalid="true" @enderror>
            </div>
        </div>

        <!-- Password -->
        <div class="auth-field">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="auth-link-small" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="auth-input @error('password') is-invalid @enderror">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                </span>
                <input id="password"
                       type="password"
                       name="password"
                       class="auth-input-control"
                       placeholder="Your password"
                       required
                       autocomplete="current-password">
                <button type="button" class="auth-password-toggle" data-target="password" aria-label="Show password">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-show" aria-hidden="true"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="toggle-hide" aria-hidden="true"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password')
                <div class="auth-field-error">{{ $message }}</div>
            @enderror
            <div class="auth-capslock d-none" id="capslock-warning" role="status">Caps Lock is on.</div>
        </div>

        <!-- Remember me -->
        <div class="form-check form-check-primary auth-remember">
            <input class="form-check-input" type="checkbox" id="remember_me" name="remember" value="1" @checked(old('remember'))>
            <label class="form-check-label" for="remember_me">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn btn-primary auth-submit" id="login-submit">
            <span class="auth-submit-label">Sign in</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </form>

@endsection
