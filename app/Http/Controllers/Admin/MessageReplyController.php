<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMessageReplyRequest;
use App\Jobs\SendMessageReply;
use App\Models\Message;
use App\Models\MessageReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Replying to inbox messages (ADM-3): each reply is kept in the message's thread and emailed to the
 * visitor by the queued SendMessageReply job, which records whether it was sent.
 */
class MessageReplyController extends Controller
{
    public function store(StoreMessageReplyRequest $request, Message $message): RedirectResponse
    {
        $reply = new MessageReply($request->safe()->only(['subject', 'body']));
        $reply->message()->associate($message);
        $reply->user()->associate($request->user());
        $reply->save();

        $message->forceFill(['read_at' => $message->read_at ?? now(), 'replied_at' => now()])->save();

        SendMessageReply::dispatch($reply);

        return redirect()->route('admin.messages.show', $message)
            ->with('status', "Your reply to {$message->name} is being sent.");
    } // End method

    public function retry(Message $message, MessageReply $reply): RedirectResponse
    {
        Gate::authorize('update', $message);

        // Scoped binding already makes sure the reply belongs to this message; only failed replies are retried.
        abort_unless($reply->failed_at !== null, 404);

        $reply->forceFill(['failed_at' => null, 'error' => null])->save();

        SendMessageReply::dispatch($reply);

        return redirect()->route('admin.messages.show', $message)
            ->with('status', 'Trying to send your reply again.');
    } // End method
}
