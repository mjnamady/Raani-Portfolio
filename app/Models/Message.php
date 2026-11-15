<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A message from a visitor (CON-1, CON-2): sent from a "Get in touch" form, a project page or the
 * chat popup. Only the visitor's own fields are fillable; source, project, IP and status are set
 * on the server.
 */
#[Fillable(['name', 'email', 'phone', 'subject', 'body'])]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    public const SOURCE_CONTACT = 'contact';

    public const SOURCE_CHAT = 'chat';

    public const SOURCE_PROJECT = 'project';

    /**
     * Each source and its label in the admin inbox.
     *
     * @var array<string, string>
     */
    public const SOURCES = [
        self::SOURCE_CONTACT => 'Contact',
        self::SOURCE_CHAT => 'Chat',
        self::SOURCE_PROJECT => 'Project',
    ];

    public const MAX_BODY_LENGTH = 5000;

    /**
     * Visitor IP and browser details are cleared from messages older than this (privacy).
     */
    public const KEEP_VISITOR_DATA_DAYS = 90;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The owner's replies, oldest first.
     *
     * @return HasMany<MessageReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(MessageReply::class)->oldest()->oldest('id');
    }

    /**
     * @param  Builder<Message>  $query
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * @param  Builder<Message>  $query
     */
    public function scopeFromSource(Builder $query, string $source): void
    {
        $query->where('source', $source);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * The source's label, e.g. "Chat".
     */
    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst((string) $this->source);
    }

    /**
     * The subject, or the start of the message when there is none.
     */
    public function headline(): string
    {
        return filled($this->subject) ? $this->subject : str($this->body)->squish()->limit(60)->toString();
    }
}
