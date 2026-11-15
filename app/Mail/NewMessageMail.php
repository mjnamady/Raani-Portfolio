<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Tells the owner about a new message (CON-1). Queued; replying goes straight to the visitor.
 */
class NewMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * A message deleted before the mail is sent needs no mail.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Tries before the queued mail counts as failed; see backoff() for the pauses in between.
     */
    public int $tries = 5;

    /**
     * Seconds to wait before each retry, so an SMTP rate limit or a short outage sorts itself out.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [15, 60, 300, 900];
    }

    /**
     * Create a new message instance.
     */
    public function __construct(public Message $inboxMessage) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inboxMessage->email, $this->inboxMessage->name)],
            subject: Str::limit('New '.strtolower($this->inboxMessage->sourceLabel()).' message from '.$this->inboxMessage->name.': '.$this->inboxMessage->headline(), 150),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.new_message',
        );
    }
}
