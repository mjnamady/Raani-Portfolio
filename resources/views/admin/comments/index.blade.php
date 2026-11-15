@extends('admin.layout.admin_dashboard_temp')

@push('styles')
    <link href="{{ asset('backend/custom/css/comments.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')

@php
    $tabQuery = fn (string $tab) => array_filter(['status' => $tab === 'pending' ? null : $tab, 'post' => $filteredPost?->id]);
    $replyTo = (string) old('reply_to');
@endphp

<div class="middle-content container-xxl p-0">

    @include('admin.posts.partials.page_header', [
        'trail' => [['Comments', null]],
        'actions' => [['url' => route('admin.posts.index'), 'label' => '← All posts', 'style' => 'light']],
    ])

    <div class="layout-top-spacing">

        <div class="cm-head">
            <nav class="cm-tabs" aria-label="Filter comments">
                @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'all' => 'All'] as $key => $label)
                    <a href="{{ route('admin.comments.index', $tabQuery($key)) }}" class="{{ $tab === $key ? 'is-active' : '' }} {{ $key === 'pending' && $counts['pending'] ? 'has-pending' : '' }}" @if ($tab === $key) aria-current="page" @endif>
                        {{ $label }} <span>{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </nav>

            @if ($filteredPost)
                <div class="cm-filter">
                    On <strong>{{ $filteredPost->title }}</strong>
                    <a href="{{ route('admin.comments.index', array_filter(['status' => $tab === 'pending' ? null : $tab])) }}" aria-label="Show comments on every post">✕ Show all posts</a>
                </div>
            @endif
        </div>

        <div class="cm-queue">
            @forelse ($comments as $comment)
                <article class="cm-card {{ $comment->isApproved() ? 'is-approved' : 'is-pending' }} {{ $comment->is_owner ? 'is-owner' : '' }}" id="comment-{{ $comment->id }}">
                    <span class="cm-avatar" aria-hidden="true">{{ $comment->initials() }}</span>

                    <div class="cm-main">
                        <div class="cm-meta">
                            <strong>{{ $comment->author_name }}</strong>
                            @if ($comment->is_owner)
                                <span class="cm-badge cm-badge-owner">You</span>
                            @else
                                <a href="mailto:{{ str_replace('%40', '@', rawurlencode($comment->author_email)) }}" class="cm-email">{{ $comment->author_email }}</a>
                            @endif
                            <span class="cm-badge {{ $comment->isApproved() ? 'cm-badge-approved' : 'cm-badge-pending' }}">{{ $comment->isApproved() ? 'Approved' : 'Pending' }}</span>
                        </div>
                        <div class="cm-context">
                            {{ $comment->created_at->format('j M Y, H:i') }}
                            · on <a href="{{ route('admin.comments.index', array_filter(['status' => $tab === 'pending' ? null : $tab, 'post' => $comment->post_id])) }}">{{ Str::limit($comment->post->title, 60) }}</a>
                            @if ($comment->parent)
                                · replying to {{ $comment->parent->author_name }}
                            @endif
                            @if ($comment->post->isLive())
                                · <a href="{{ route('blog.show', $comment->post->slug) }}#comments" target="_blank" rel="noopener">view post</a>
                            @endif
                        </div>

                        <p class="cm-body">{{ $comment->body }}</p>

                        <div class="cm-actions">
                            @if ($comment->isApproved())
                                <form method="POST" action="{{ route('admin.comments.unapprove', $comment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-light-dark btn-sm">Unapprove</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                            @endif

                            @unless ($comment->is_owner)
                                <button type="button" class="btn btn-light-primary btn-sm" data-reply-toggle="reply-{{ $comment->id }}" aria-expanded="{{ $replyTo === (string) $comment->id ? 'true' : 'false' }}" aria-controls="reply-{{ $comment->id }}">Reply</button>
                            @endunless

                            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" data-confirm="Delete this comment by &quot;{{ $comment->author_name }}&quot;?{{ $comment->parent_id ? '' : ' Its replies will be deleted too.' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light-danger btn-sm">Delete</button>
                            </form>
                        </div>

                        @unless ($comment->is_owner)
                            <form method="POST" action="{{ route('admin.comments.reply', $comment) }}" class="cm-reply" id="reply-{{ $comment->id }}" @if ($replyTo !== (string) $comment->id) hidden @endif>
                                @csrf
                                <label for="reply-body-{{ $comment->id }}" class="visually-hidden">Your reply to {{ $comment->author_name }}</label>
                                <textarea class="form-control @if ($replyTo === (string) $comment->id) @error('body', 'reply') is-invalid @enderror @endif" id="reply-body-{{ $comment->id }}" name="body" rows="3" maxlength="{{ \App\Models\Comment::MAX_BODY_LENGTH }}" placeholder="Write your reply…" required>{{ $replyTo === (string) $comment->id ? old('body') : '' }}</textarea>
                                @if ($replyTo === (string) $comment->id)
                                    @error('body', 'reply')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                                <div class="cm-reply-foot">
                                    <small>{{ $comment->isApproved() ? 'Your reply appears on the post straight away.' : 'Replying also approves this comment.' }}</small>
                                    <button type="submit" class="btn btn-primary btn-sm">Post reply</button>
                                </div>
                            </form>
                        @endunless
                    </div>
                </article>
            @empty
                <div class="cm-empty">
                    @if ($tab === 'pending')
                        <span aria-hidden="true">✓</span>
                        <p>All caught up: no comments are waiting for approval.</p>
                    @else
                        <p>No comments here yet.</p>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($comments->hasPages())
            <div class="mt-3">
                {{ $comments->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/custom/js/comments.js') }}"></script>
@endpush
