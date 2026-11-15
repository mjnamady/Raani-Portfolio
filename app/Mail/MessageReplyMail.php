<?php

namespace App\Mail;

use App\Models\MessageReply;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The owner's reply as the visitor receives it, with their original message quoted. Sent by the
 * SendMessageReply job (which handles queueing and retries), so this mailable is not queued itself.
 */
class MessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public MessageReply $reply) {}

    /**
     * Get the message envelope. Replies from the visitor go to the owner's admin email.
     */
    public function envelope(): Envelope
    {
        $ownerEmail = $this->reply->user?->email
            ?? User::query()->where('is_admin', true)->orderBy('id')->value('email');

        return new Envelope(
            from: new Address((string) config('mail.from.address'), SiteSettings::group('site')['name']),
            replyTo: $ownerEmail ? [new Address($ownerEmail, SiteSettings::group('site')['name'])] : [],
            subject: $this->reply->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.message_reply',
            with: [
                'original' => $this->reply->message,
                'siteName' => SiteSettings::group('site')['name'],
            ],
        );
    }
}
