<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Visitor comments on blog posts (BLG-9, V-5). Every comment waits for the owner's approval.
 */
class CommentController extends Controller
{
    public const THANK_YOU = 'Thanks! Your comment will appear once it has been approved.';

    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $backToForm = redirect()->to(route('blog.show', $post->slug).'#comment-form');

        // Bots that fill the hidden field get the normal answer, but nothing is saved (CON-4).
        // Logged (no personal data) so a discarded comment never goes unnoticed.
        if (filled($request->input(StoreCommentRequest::HONEYPOT))) {
            Log::info('Comment discarded by the honeypot.', ['post' => $post->id]);

            return $backToForm->with('comment_status', self::THANK_YOU);
        }

        if (! $post->allow_comments) {
            return $backToForm->withErrors(['body' => 'Comments are closed on this post.'], 'comment');
        }

        $comment = new Comment($request->safe()->only(['author_name', 'author_email', 'body']));
        $comment->post_id = $post->id;
        $comment->parent_id = $request->validated('parent_id');
        $comment->status = Comment::STATUS_PENDING;
        $comment->ip_address = $request->ip();
        // mb_scrub(): invalid UTF-8 in the header would make MySQL refuse the row.
        $comment->user_agent = Str::limit(mb_scrub((string) $request->userAgent(), 'UTF-8'), 250, '');
        $comment->save();

        return $backToForm->with('comment_status', self::THANK_YOU);
    } // End method
}
