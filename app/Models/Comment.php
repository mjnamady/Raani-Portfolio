<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A visitor comment on a blog post, or the owner's reply (BLG-9). Only approved comments are public.
 */
#[Fillable(['author_name', 'author_email', 'body'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const MAX_BODY_LENGTH = 2000;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function isApproved(): bool
    {
        return $this->status === static::STATUS_APPROVED;
    }

    /**
     * Mark the comment approved (or back to pending) without saving.
     */
    public function setApproved(bool $approved): static
    {
        $this->status = $approved ? static::STATUS_APPROVED : static::STATUS_PENDING;
        $this->approved_at = $approved ? ($this->approved_at ?? now()) : null;

        return $this;
    }

    /**
     * Up to two initials for the avatar circle, e.g. "Court Henry" becomes "CH".
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim($this->author_name), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];

        return Str::upper(collect([reset($words), count($words) > 1 ? end($words) : null])
            ->filter()
            ->map(fn (string $word) => mb_substr($word, 0, 1))
            ->implode(''));
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->oldest()->orderBy('id');
    }

    /**
     * @param  Builder<Comment>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', static::STATUS_APPROVED);
    }

    /**
     * @param  Builder<Comment>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', static::STATUS_PENDING);
    }
}
