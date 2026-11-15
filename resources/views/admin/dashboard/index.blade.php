@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/dashboard.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $cards = [
        ['label' => 'Unread messages', 'value' => $counts['unread_messages'], 'url' => route('admin.messages.index', ['status' => 'unread']), 'alert' => $counts['unread_messages'] > 0],
        ['label' => 'Pending comments', 'value' => $counts['pending_comments'], 'url' => route('admin.comments.index'), 'alert' => $counts['pending_comments'] > 0],
        ['label' => 'Published projects', 'value' => $counts['projects'], 'url' => route('admin.projects.index'), 'note' => $counts['project_drafts'] ? "+{$counts['project_drafts']} ".Str::plural('draft', $counts['project_drafts']) : null],
        ['label' => 'Published posts', 'value' => $counts['posts'], 'url' => route('admin.posts.index'), 'note' => $counts['post_drafts'] ? "+{$counts['post_drafts']} ".Str::plural('draft', $counts['post_drafts']) : null],
        ['label' => 'Services', 'value' => $counts['services'], 'url' => route('admin.services')],
    ];
@endphp

<div class="middle-content container-xxl p-0">

    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Dashboard">
            <header class="header navbar navbar-expand-sm">
                <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </a>
                <div class="d-flex breadcrumb-content">
                    <div class="page-header">
                        <div class="page-title"></div>
                        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </header>
        </div>
    </div>

    <div class="layout-top-spacing">

        <!-- ===================== GREETING ===================== -->
        <div class="db-hello">
            <div>
                <h3>{{ $greeting }}, {{ auth()->user()->name }}</h3>
                <p>{{ now()->format('l j F Y') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-light-primary">View site</a>
                <a href="{{ route('admin.messages.index') }}" class="btn btn-primary">Messages</a>
            </div>
        </div>

        <!-- ===================== COUNTS ===================== -->
        <div class="db-cards">
            @foreach ($cards as $card)
                <a href="{{ $card['url'] }}" class="db-card {{ ! empty($card['alert']) ? 'is-alert' : '' }}">
                    <span class="db-card-value">{{ $card['value'] }}</span>
                    <span class="db-card-label">{{ $card['label'] }}</span>
                    @if (! empty($card['note']))
                        <span class="db-card-note">{{ $card['note'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="row g-4">
            <!-- ===================== LATEST MESSAGES ===================== -->
            <div class="col-xl-8">
                <section class="db-panel" aria-labelledby="db-latest">
                    <header class="db-panel-head">
                        <h5 id="db-latest">Latest messages</h5>
                        <a href="{{ route('admin.messages.index') }}">Open inbox →</a>
                    </header>
                    @forelse ($latestMessages as $message)
                        <a href="{{ route('admin.messages.show', $message) }}" class="db-message {{ $message->isUnread() ? 'is-unread' : '' }}">
                            <span class="db-dot" aria-hidden="true"></span>
                            <span class="db-message-main">
                                <strong>{{ $message->name }}</strong>
                                <span>{{ $message->headline() }}</span>
                            </span>
                            <span class="db-chip db-chip-{{ $message->source }}">{{ $message->sourceLabel() }}</span>
                            <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans(short: true) }}</time>
                            @if ($message->isUnread())
                                <span class="visually-hidden">Unread</span>
                            @endif
                        </a>
                    @empty
                        <p class="db-empty">No messages yet. Messages from the contact forms, project pages and the chat popup appear here.</p>
                    @endforelse
                </section>
            </div>

            <div class="col-xl-4">
                <!-- ===================== EMAIL HEALTH ===================== -->
                <section class="db-panel" aria-labelledby="db-email">
                    <header class="db-panel-head">
                        <h5 id="db-email">Email</h5>
                    </header>
                    @if ($waitingEmails > 0)
                        <p class="db-health is-warning">
                            <strong>{{ $waitingEmails }} {{ Str::plural('email', $waitingEmails) }} {{ $waitingEmails === 1 ? 'is' : 'are' }} waiting to be sent.</strong>
                            Is the queue worker running? Start it with <code>php artisan queue:work</code>.
                        </p>
                    @endif
                    @if ($failedReplies > 0)
                        <p class="db-health is-failed">
                            <strong>{{ $failedReplies }} {{ Str::plural('reply', $failedReplies) }} could not be sent.</strong>
                            <a href="{{ route('admin.messages.show', $failedReplyMessageId) }}">Open {{ $failedReplies === 1 ? 'it' : 'the latest' }} and retry</a>
                        </p>
                    @endif
                    @if ($waitingEmails === 0 && $failedReplies === 0)
                        <p class="db-health is-ok">Emails are sending.</p>
                    @endif
                </section>

                <!-- ===================== QUICK LINKS ===================== -->
                <section class="db-panel mt-4" aria-labelledby="db-links">
                    <header class="db-panel-head">
                        <h5 id="db-links">Quick links</h5>
                    </header>
                    <nav class="db-links">
                        <a href="{{ route('admin.projects.create') }}">+ New project</a>
                        <a href="{{ route('admin.posts.create') }}">+ New post</a>
                        <a href="{{ route('admin.settings.index') }}">Site Settings</a>
                        <a href="{{ route('admin.contact.index') }}">Contact Page</a>
                        <a href="{{ route('admin.banner') }}">Banner</a>
                    </nav>
                </section>
            </div>
        </div>

    </div>

</div>

@endsection
