@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/messages.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@use('App\Models\Message')
@use('App\Support\ContactDetails')

@php
    $tabs = ['all' => 'All', 'unread' => 'Unread', 'replied' => 'Replied'];
@endphp

<div class="middle-content container-xxl p-0">

    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Messages">
            <header class="header navbar navbar-expand-sm">
                <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </a>
                <div class="d-flex breadcrumb-content">
                    <div class="page-header">
                        <div class="page-title"></div>
                        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.messages.index') }}">Inbox</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Messages</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </header>
        </div>
    </div>

    <div class="layout-top-spacing">
        @if ($waitingEmails > 0)
            <div class="mx-warning" role="alert">
                <strong>{{ $waitingEmails }} {{ Str::plural('email', $waitingEmails) }} {{ $waitingEmails === 1 ? 'is' : 'are' }} waiting to be sent.</strong>
                Is the queue worker running? Start it with <code>php artisan queue:work</code>.
            </div>
        @endif

        <div class="mx-inbox {{ $selected ? 'has-selection' : '' }}">

            <!-- ===================== LIST ===================== -->
            <section class="mx-list" aria-label="Messages">
                <div class="mx-toolbar">
                    <nav class="mx-tabs" aria-label="Filter by status">
                        @foreach ($tabs as $key => $label)
                            <a href="{{ route('admin.messages.index', array_filter(['status' => $key === 'all' ? null : $key, 'source' => $source])) }}"
                               class="{{ $status === $key ? 'active' : '' }}" @if ($status === $key) aria-current="page" @endif>
                                {{ $label }} <span>{{ $counts[$key] }}</span>
                            </a>
                        @endforeach
                    </nav>
                    <nav class="mx-sources" aria-label="Filter by source">
                        <a href="{{ route('admin.messages.index', array_filter(['status' => $status === 'all' ? null : $status])) }}" class="{{ $source === null ? 'active' : '' }}">Any source</a>
                        @foreach (Message::SOURCES as $key => $label)
                            <a href="{{ route('admin.messages.index', array_filter(['status' => $status === 'all' ? null : $status, 'source' => $key])) }}" class="mx-chip-{{ $key }} {{ $source === $key ? 'active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>

                @forelse ($messages as $message)
                    <a href="{{ route('admin.messages.show', ['message' => $message] + $filters) }}"
                       class="mx-row {{ $message->isUnread() ? 'is-unread' : '' }} {{ $selected?->is($message) ? 'is-selected' : '' }}"
                       @if ($selected?->is($message)) aria-current="true" @endif>
                        <span class="mx-dot" aria-hidden="true"></span>
                        <span class="mx-row-main">
                            <span class="mx-row-top">
                                <strong>{{ $message->name }}</strong>
                                <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->isToday() ? $message->created_at->format('H:i') : $message->created_at->format('j M') }}</time>
                            </span>
                            <span class="mx-row-subject">{{ $message->headline() }}</span>
                            <span class="mx-row-meta">
                                <span class="mx-chip mx-chip-{{ $message->source }}">{{ $message->sourceLabel() }}</span>
                                @if ($message->replied_at)
                                    <span class="mx-replied">Replied</span>
                                @endif
                                @if ($message->isUnread())
                                    <span class="visually-hidden">Unread</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="mx-empty">
                        <strong>{{ $status === 'unread' ? 'All caught up' : 'No messages yet' }}</strong>
                        <p>Messages sent from the contact forms, project pages and the chat popup appear here.</p>
                    </div>
                @endforelse

                @if ($messages->hasPages())
                    <div class="mx-pages">{{ $messages->links() }}</div>
                @endif
            </section>

            <!-- ===================== READING PANE ===================== -->
            <section class="mx-reader" aria-label="Message">
                @if ($selected)
                    @php
                        $replySubject = 'Re: '.($selected->subject ?: 'Your message');
                        $replyHref = ContactDetails::mailtoHref($selected->email).'?subject='.rawurlencode($replySubject);
                        $telHref = $selected->phone ? ContactDetails::telHref($selected->phone) : null;
                    @endphp
                    <a href="{{ route('admin.messages.index', $filters) }}" class="mx-back">← All messages</a>

                    <header class="mx-reader-head">
                        <div>
                            <span class="mx-chip mx-chip-{{ $selected->source }}">{{ $selected->sourceLabel() }}</span>
                            @if ($selected->replied_at)
                                <span class="mx-replied">Replied {{ $selected->replied_at->format('j M Y') }}</span>
                            @endif
                            <h4>{{ $selected->subject ?: 'No subject' }}</h4>
                        </div>
                        <time datetime="{{ $selected->created_at->toIso8601String() }}">{{ $selected->created_at->format('j M Y, H:i') }}</time>
                    </header>

                    <dl class="mx-sender">
                        <div><dt>From</dt><dd>{{ $selected->name }}</dd></div>
                        <div><dt>Email</dt><dd><a href="{{ ContactDetails::mailtoHref($selected->email) }}">{{ $selected->email }}</a></dd></div>
                        @if ($selected->phone)
                            <div><dt>Phone</dt><dd>@if ($telHref)<a href="{{ $telHref }}">{{ $selected->phone }}</a>@else{{ $selected->phone }}@endif</dd></div>
                        @endif
                        @if ($selected->source === Message::SOURCE_PROJECT)
                            <div><dt>Project</dt><dd>
                                @if ($selected->project)
                                    <a href="{{ route('admin.projects.edit', $selected->project) }}">{{ $selected->project->title }}</a>
                                @else
                                    <em>Deleted project</em>
                                @endif
                            </dd></div>
                        @endif
                    </dl>

                    <div class="mx-body">{{ $selected->body }}</div>

                    <!-- Replies sent from the inbox, with their delivery status -->
                    @foreach ($selected->replies as $reply)
                        <article class="mx-reply is-{{ $reply->status() }}" aria-label="Your reply">
                            <header>
                                <strong>You replied</strong>
                                <span class="mx-reply-status">
                                    @switch($reply->status())
                                        @case('sent')
                                            Sent {{ $reply->sent_at->format('j M Y, H:i') }}
                                            @break
                                        @case('failed')
                                            Failed {{ $reply->failed_at->format('j M, H:i') }}
                                            @break
                                        @default
                                            Sending…
                                    @endswitch
                                </span>
                            </header>
                            <div class="mx-reply-subject">{{ $reply->subject }}</div>
                            <div class="mx-reply-body">{{ $reply->body }}</div>
                            @if ($reply->status() === 'failed')
                                <div class="mx-reply-error">
                                    <span>{{ $reply->error }}</span>
                                    <form method="POST" action="{{ route('admin.messages.replies.retry', [$selected, $reply]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-light-danger">Retry</button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @endforeach

                    <!-- Reply box: always sent to this message's own address -->
                    @php
                        $replyErrors = $errors->getBag('reply');
                        $replyOld = fn (string $field, string $default = '') => is_string(old($field)) ? old($field) : $default;
                    @endphp
                    <form method="POST" action="{{ route('admin.messages.replies.store', $selected) }}" class="mx-composer" novalidate data-track-changes>
                        @csrf
                        <div class="mx-composer-to">
                            <span>To</span>
                            <strong>{{ $selected->name }}</strong> &lt;{{ $selected->email }}&gt;
                        </div>
                        <label for="reply-subject" class="visually-hidden">Subject</label>
                        <input type="text" class="form-control @if ($replyErrors->has('subject')) is-invalid @endif" id="reply-subject" name="subject" value="{{ $replyOld('subject', $replySubject) }}" required maxlength="150">
                        @if ($replyErrors->has('subject'))
                            <div class="invalid-feedback">{{ $replyErrors->first('subject') }}</div>
                        @endif
                        <label for="reply-body" class="visually-hidden">Your reply</label>
                        <textarea class="form-control @if ($replyErrors->has('body')) is-invalid @endif" id="reply-body" name="body" rows="6" required maxlength="{{ \App\Models\MessageReply::MAX_BODY_LENGTH }}" placeholder="Write your reply to {{ $selected->name }}…">{{ $replyOld('body') }}</textarea>
                        @if ($replyErrors->has('body'))
                            <div class="invalid-feedback">{{ $replyErrors->first('body') }}</div>
                        @endif
                        <div class="mx-composer-foot">
                            <span>{{ $selected->name }} receives it by email, with their message quoted below.</span>
                            <button type="submit" class="btn btn-primary">Send reply</button>
                        </div>
                    </form>

                    <div class="mx-actions">
                        <a href="{{ $replyHref }}" class="btn btn-light-primary">Reply in my mail app</a>

                        <form method="POST" action="{{ route('admin.messages.'.($selected->replied_at ? 'unreplied' : 'replied'), $selected) }}">
                            @csrf
                            @method('PATCH')
                            @foreach ($filters as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="btn btn-light-success">{{ $selected->replied_at ? 'Mark as not replied' : 'Mark as replied' }}</button>
                        </form>

                        <form method="POST" action="{{ route('admin.messages.unread', $selected) }}">
                            @csrf
                            @method('PATCH')
                            @foreach ($filters as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="btn btn-light-dark">Mark as unread</button>
                        </form>

                        <form method="POST" action="{{ route('admin.messages.destroy', $selected) }}" class="ms-auto" data-confirm="Delete the message from {{ $selected->name }}? This can not be undone.">
                            @csrf
                            @method('DELETE')
                            @foreach ($filters as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="btn btn-light-danger">Delete</button>
                        </form>
                    </div>
                @else
                    <div class="mx-reader-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M4 13h3l3 3h4l3 -3h3"/></svg>
                        <p>Select a message to read it.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/messages.js') }}"></script>
@endpush
