@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/profile-settings.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $passwordTabActive = $errors->updatePassword->isNotEmpty() || session('active_tab') === 'password';
@endphp

<div class="middle-content container-xxl p-0">

    <!--  BEGIN BREADCRUMBS  -->
    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Profile Settings">
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
                                <li class="breadcrumb-item"><a href="{{ route('admin.banner') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
                            </ol>
                        </nav>

                    </div>
                </div>
            </header>
        </div>
    </div>
    <!--  END BREADCRUMBS  -->

    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-md-12">

            <div class="profile-header mb-5">

                <div class="profile-backdrop-content"></div>

                <div class="profile-container">
                    <div class="profile">
                        <img id="profile-header-avatar" alt="{{ $user->name }}" src="{{ $user->profile_image_url }}" class="rounded-circle">
                        <div class="media-body">
                            <h4>{{ $user->name }}</h4>
                            <p>{{ '@'.($user->username ?? 'admin') }}</p>
                        </div>
                    </div>
                </div>

                <div class="profile-info">

                    <div class="row justify-content-between">

                        <div class="col-xl-5 col-lg-12 col-md-12 col-12"></div>

                        <div class="col-xl-7 col-lg-12 col-md-12 col-12">

                            <div class="profile-menu">

                                <ul class="nav nav-tabs justify-content-md-end justify-content-center" id="nav-tab" role="tablist">

                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $passwordTabActive ? '' : 'active' }}" id="nav-profile-tab" data-bs-toggle="pill" data-bs-target="#nav-profile" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="{{ $passwordTabActive ? 'false' : 'true' }}">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                                            <span>Profile</span>
                                        </a>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link {{ $passwordTabActive ? 'active' : '' }}" id="nav-password-tab" data-bs-toggle="pill" data-bs-target="#nav-password" href="#nav-password" role="tab" aria-controls="nav-password" aria-selected="{{ $passwordTabActive ? 'true' : 'false' }}">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-lock"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 0 1 5 5v3a3 3 0 0 1 3 3v6a3 3 0 0 1 -3 3h-10a3 3 0 0 1 -3 -3v-6a3 3 0 0 1 3 -3v-3a5 5 0 0 1 5 -5m0 12a2 2 0 0 0 -1.995 1.85l-.005 .15a2 2 0 1 0 2 -2m0 -10a3 3 0 0 0 -3 3v3h6v-3a3 3 0 0 0 -3 -3" /></svg>
                                            <span>Change Password</span>
                                        </a>
                                    </li>

                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-xl-12 col-lg-12 col-md-12">

            <div class="profile-content-area">

                <div class="tab-content" id="nav-tabContent">

                    <!-- ===================== PROFILE TAB ===================== -->
                    <div class="tab-pane fade {{ $passwordTabActive ? '' : 'show active' }}" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">

                        <form method="POST" action="{{ route('admin.profile.settings.update') }}" enctype="multipart/form-data" novalidate data-track-changes>
                            @csrf
                            @method('PATCH')

                            <div class="row">

                                <!-- Image + summary -->
                                <div class="col-xxl-4 col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                                    <div class="profile-settings-card ps-summary">

                                        <div class="ps-avatar-preview">
                                            <img id="profile-image-preview"
                                                 src="{{ $user->profile_image_url }}"
                                                 data-original="{{ $user->profile_image_url }}"
                                                 alt="Profile image preview">
                                            <span class="badge badge-primary ps-avatar-badge">New</span>
                                        </div>

                                        <h4>{{ $user->name }}</h4>
                                        <p>{{ $user->email }}</p>

                                        <div class="mt-4 text-start">
                                            <label for="profile_image">Profile image</label>
                                            <input type="file"
                                                   class="form-control @error('profile_image') is-invalid @enderror"
                                                   id="profile_image"
                                                   name="profile_image"
                                                   accept="image/jpeg,image/png,image/webp">
                                            <div id="profile-image-client-error" class="invalid-feedback d-none"></div>
                                            @error('profile_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">JPG, PNG or WebP, up to 2 MB. It is cropped to a 400&times;400 square.</div>
                                            <button type="button" id="profile-image-reset" class="btn btn-light-dark btn-sm mt-2 d-none">Undo image change</button>
                                        </div>

                                        <ul class="list-group mt-4">
                                            <li class="list-group-item">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-at"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M16 12v1.5a2.5 2.5 0 0 0 5 0v-1.5a9 9 0 1 0 -5.5 8.28" /></svg>
                                                {{ $user->username ?? 'No username set' }}
                                            </li>
                                            <li class="list-group-item">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                                                Admin since {{ $user->created_at?->format('d M Y') }}
                                            </li>
                                            <li class="list-group-item">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-clock"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 7v5l3 3" /></svg>
                                                Last updated {{ $user->updated_at?->diffForHumans() }}
                                            </li>
                                        </ul>

                                    </div>
                                </div>

                                <!-- Profile details -->
                                <div class="col-xxl-8 col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                                    <div class="profile-settings-card">

                                        <h5>Profile details</h5>
                                        <p class="card-subtitle">Update your name, username, email address and profile image.</p>

                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label for="name">Full name</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label for="username">Username</label>
                                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="username">
                                                @error('username')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Letters, numbers, dashes and underscores. You can log in with it.</div>
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label for="email">Email address</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label for="profile_current_password">Current password</label>
                                                <div class="form-action-password">
                                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="profile_current_password" name="current_password" autocomplete="current-password" data-track-ignore>
                                                    <button type="button" class="form-password-toggle" data-target="profile_current_password" aria-label="Show password">
                                                        @include('admin.profile.partials.eye-icons')
                                                    </button>
                                                    @error('current_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-text">Only needed when you change your email address.</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            @include('admin.layout.components.save_bar', ['label' => 'Save changes'])
                        </form>

                    </div>

                    <!-- ===================== CHANGE PASSWORD TAB ===================== -->
                    <div class="tab-pane fade {{ $passwordTabActive ? 'show active' : '' }}" id="nav-password" role="tabpanel" aria-labelledby="nav-password-tab" tabindex="0">

                        <div class="row justify-content-center">
                            <div class="col-xxl-6 col-xl-8 col-lg-10 col-md-12 mb-4">
                                <div class="profile-settings-card">

                                    <h5>Change password</h5>
                                    <p class="card-subtitle">Enter your current password, then choose a new one.</p>

                                    <form method="POST" action="{{ route('admin.profile.password.update') }}" novalidate data-track-changes>
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-4">
                                            <label for="current_password">Current password</label>
                                            <div class="form-action-password">
                                                <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password" data-track-ignore>
                                                <button type="button" class="form-password-toggle" data-target="current_password" aria-label="Show password">
                                                    @include('admin.profile.partials.eye-icons')
                                                </button>
                                                @error('current_password', 'updatePassword')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="password">New password</label>
                                            <div class="form-action-password">
                                                <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password" required minlength="8" autocomplete="new-password" aria-describedby="password-rules">
                                                <button type="button" class="form-password-toggle" data-target="password" aria-label="Show password">
                                                    @include('admin.profile.partials.eye-icons')
                                                </button>
                                                @error('password', 'updatePassword')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <ul class="password-rules" id="password-rules">
                                                <li data-rule="length">At least 8 characters</li>
                                                <li data-rule="case">Upper and lower case letters</li>
                                                <li data-rule="number">At least one number</li>
                                                <li data-rule="symbol">At least one symbol</li>
                                            </ul>
                                        </div>

                                        <div class="mb-4">
                                            <label for="password_confirmation">Confirm new password</label>
                                            <div class="form-action-password">
                                                <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                                                <button type="button" class="form-password-toggle" data-target="password_confirmation" aria-label="Show password">
                                                    @include('admin.profile.partials.eye-icons')
                                                </button>
                                                @error('password_confirmation', 'updatePassword')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div id="password-match-hint" class="form-text"></div>
                                        </div>

                                        @include('admin.layout.components.save_bar', ['label' => 'Update password'])
                                    </form>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/profile-settings.js') }}"></script>
@endpush
