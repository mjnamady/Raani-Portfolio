<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Message;
use App\Models\MessageReply;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The admin dashboard (ADM-2): live counts, the latest 5 messages, email health and quick links.
 * Nothing is cached, so the numbers are always current.
 */
class DashboardController extends Controller
{
    public const LATEST_MESSAGES = 5;

    public function index(): View
    {
        Gate::authorize('viewAny', Message::class);

        $failedReply = MessageReply::query()->whereNotNull('failed_at')->latest('failed_at')->first(['id', 'message_id']);

        return view('admin.dashboard.index', [
            'counts' => [
                'unread_messages' => Message::query()->unread()->count(),
                'pending_comments' => Comment::query()->pending()->count(),
                'projects' => Project::query()->published()->count(),
                'project_drafts' => Project::query()->where('status', Project::STATUS_DRAFT)->count(),
                'posts' => Post::query()->published()->count(),
                'post_drafts' => Post::query()->where('status', Post::STATUS_DRAFT)->count(),
                'services' => Service::query()->active()->count(),
            ],
            'latestMessages' => Message::query()->latest()->latest('id')->limit(self::LATEST_MESSAGES)->get(),
            'waitingEmails' => MessageController::waitingEmails(),
            'failedReplies' => MessageReply::query()->whereNotNull('failed_at')->count(),
            'failedReplyMessageId' => $failedReply?->message_id,
        ]);
    } // End method
}
