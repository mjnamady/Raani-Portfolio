{{--
    One approved comment in the theme's comment markup (BLG-9).
    $comment  the Comment
    $isReply  replies are indented one level (pl--100) and get no reply link
--}}
<article class="single-comment-audience {{ $isReply ? 'pl--100' : '' }}" id="comment-{{ $comment->id }}">
    <div class="author-image tmponhover">
        <span class="comment-avatar {{ $comment->is_owner ? 'is-owner' : '' }}" aria-hidden="true">{{ $comment->initials() }}</span>
    </div>
    <div class="right-area-commnet">
        <div class="top-area-comment">
            <div class="left">
                <h6 class="title">
                    {{ $comment->author_name }}
                    @if ($comment->is_owner)
                        <span class="comment-author-badge">Author</span>
                    @endif
                </h6>
                <span><time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->format('F j, Y') }}</time></span>
            </div>
        </div>
        <p class="disc comment-body">{{ $comment->body }}</p>
        @if (! $isReply && $post->allow_comments)
            <a href="{{ route('blog.show', ['slug' => $post->slug, 'reply' => $comment->id]) }}#comment-form" class="reply-btn" aria-label="Reply to {{ $comment->author_name }}">reply</a>
        @endif
    </div>
</article>
