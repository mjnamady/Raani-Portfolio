<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyCommentRequest;
use App\Models\BannerSection;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Comment moderation (BLG-9, A-7): approve, unapprove, reply and delete.
 */
class CommentController extends Controller
{
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Comment::class);

        $status = match ($request->query('status')) {
            'approved' => Comment::STATUS_APPROVED,
            'all' => null,
            default => Comment::STATUS_PENDING,
        };
        $post = $request->query('post');
        $postId = is_string($post) && ctype_digit($post) ? (int) $post : null;
        $filteredPost = $postId ? Post::query()->select(['id', 'title', 'slug'])->find($postId) : null;

        return view('admin.comments.index', [
            'comments' => Comment::query()
                ->with(['post:id,title,slug,status,published_at', 'parent:id,author_name'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($filteredPost, fn ($query) => $query->where('post_id', $filteredPost->id))
                ->latest()
                ->orderByDesc('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'tab' => $status ?? 'all',
            'filteredPost' => $filteredPost,
            'counts' => [
                'pending' => Comment::query()->pending()->when($filteredPost, fn ($query) => $query->where('post_id', $filteredPost->id))->count(),
                'approved' => Comment::query()->approved()->when($filteredPost, fn ($query) => $query->where('post_id', $filteredPost->id))->count(),
                'all' => Comment::query()->when($filteredPost, fn ($query) => $query->where('post_id', $filteredPost->id))->count(),
            ],
        ]);
    } // End method

    public function approve(Comment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);

        $comment->setApproved(true)->save();

        return back()->with('status', 'The comment is now shown on the post.');
    } // End method

    public function unapprove(Comment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);

        $comment->setApproved(false)->save();

        return back()->with('status', 'The comment is hidden again and back in the pending list.');
    } // End method

    /**
     * Reply as the owner. Replies stay one level deep, so a reply to a reply joins its thread.
     * Replying to a pending comment approves it, since the reply would make no sense on its own.
     */
    public function reply(ReplyCommentRequest $request, Comment $comment): RedirectResponse
    {
        DB::transaction(function () use ($request, $comment) {
            if (! $comment->isApproved()) {
                $comment->setApproved(true)->save();
            }

            $reply = new Comment([
                'author_name' => BannerSection::query()->value('name') ?? $request->user()->name,
                'author_email' => $request->user()->email,
                'body' => $request->validated('body'),
            ]);
            $reply->post_id = $comment->post_id;
            $reply->parent_id = $comment->parent_id ?? $comment->id;
            $reply->is_owner = true;
            $reply->setApproved(true);
            $reply->save();
        });

        return back()->with('status', 'Your reply has been posted.');
    } // End method

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        // Replies go with it (cascade).
        $comment->delete();

        return back()->with('status', 'The comment has been deleted.');
    } // End method
}
