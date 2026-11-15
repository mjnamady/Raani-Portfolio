<?php

namespace App\Jobs;

use App\Mail\MessageReplyMail;
use App\Models\MessageReply;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Emails the owner's reply to the visitor and records the delivery status (ADM-3). Retried with
 * growing pauses, so an SMTP rate limit or a short outage sorts itself out.
 */
class SendMessageReply implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * A reply deleted with its message needs no mail.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(public MessageReply $reply) {}

    /**
     * Seconds to wait before each retry.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [15, 60, 300, 900];
    }

    /**
     * Execute the job. The recipient is always the stored address of the message being answered.
     */
    public function handle(): void
    {
        if ($this->reply->sent_at !== null) {
            return;
        }

        $message = $this->reply->message;

        Mail::to($message->email, $message->name)->send(new MessageReplyMail($this->reply));

        $this->reply->forceFill(['sent_at' => now(), 'failed_at' => null, 'error' => null])->save();
    }

    /**
     * Every try failed: show it in the inbox with a short reason (never credentials or the payload).
     */
    public function failed(Throwable $exception): void
    {
        $this->reply->forceFill([
            'failed_at' => now(),
            'error' => Str::limit(class_basename($exception).': '.Str::squish($exception->getMessage()), 240),
        ])->save();
    }
}
