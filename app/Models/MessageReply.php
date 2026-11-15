<?php

namespace App\Models;

use Database\Factories\MessageReplyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The owner's reply to an inbox message, emailed to the visitor (ADM-3). Only the subject and body
 * come from the form; the message, author and delivery status are set on the server.
 */
#[Fillable(['subject', 'body'])]
class MessageReply extends Model
{
    /** @use HasFactory<MessageReplyFactory> */
    use HasFactory;

    public const MAX_BODY_LENGTH = 5000;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Message, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * "sending", "sent" or "failed".
     */
    public function status(): string
    {
        return match (true) {
            $this->sent_at !== null => 'sent',
            $this->failed_at !== null => 'failed',
            default => 'sending',
        };
    }
}
