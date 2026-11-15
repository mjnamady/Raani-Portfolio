<?php

namespace App\Models;

use App\Support\RichText;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'category_id', 'title', 'slug', 'excerpt', 'body', 'featured_image_alt',
    'author_name', 'status', 'published_at', 'allow_comments', 'meta_title', 'meta_description',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /**
     * Versioned cache key for the home "Blog and News" cards (stored as plain arrays).
     */
    public const CACHE_KEY = 'home.blog.v1';

    /**
     * How many posts the home "Blog and News" section shows (HOME-10).
     */
    public const HOME_LIMIT = 3;

    /**
     * Theme demo images live under this folder; they are read-only and never deleted.
     */
    public const THEME_IMAGE_PREFIX = 'frontend/assets/images/';

    /**
     * Image on the post page: the theme's 850x440 thumbnail at double size.
     */
    public const FEATURED_WIDTH = 1700;

    public const FEATURED_HEIGHT = 880;

    /**
     * Image on the blog and home cards: the theme's 410x294 card at double size.
     */
    public const CARD_WIDTH = 820;

    public const CARD_HEIGHT = 588;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'allow_comments' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: unique slug, sanitized body, a publish date for published
     * posts, and cache clearing on change.
     */
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            $post->slug = static::uniqueSlug($post->slug ?: $post->title, $post->id);

            // Defence in depth: the body is sanitized whatever saved it (admin form, seeder, ...).
            if ($post->isDirty('body')) {
                $post->body = RichText::clean($post->body);
            }

            if ($post->status === static::STATUS_PUBLISHED && $post->published_at === null) {
                $post->published_at = now();
            }
        });

        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * A slug based on $text that no other post uses (ADM-7).
     */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($text), 170, '') ?: 'post';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Public URL for a stored image path: a theme demo file or an upload on the public disk.
     */
    public static function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return static::isThemePath($path) ? asset($path) : Storage::disk('public')->url($path);
    }

    /**
     * Whether the path points at a read-only theme demo file rather than an upload.
     */
    public static function isThemePath(?string $path): bool
    {
        return Str::startsWith((string) $path, static::THEME_IMAGE_PREFIX);
    }

    /**
     * The latest published posts for the home page, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Post>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->published()
            ->newestFirst()
            ->limit(static::HOME_LIMIT)
            ->get()
            ->map(fn (Post $post) => $post->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    public function isPublished(): bool
    {
        return $this->status === static::STATUS_PUBLISHED;
    }

    /**
     * Whether visitors can see the post right now: the same rule as the published() scope.
     */
    public function isLive(): bool
    {
        return $this->isPublished() && $this->published_at !== null && $this->published_at->lte(now());
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Approved top-level comments, oldest first, each with its approved replies (BLG-9).
     *
     * @return HasMany<Comment, $this>
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->approved()
            ->with(['replies' => fn ($query) => $query->approved()])
            ->oldest()
            ->orderBy('id');
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(fn () => static::publicUrl($this->featured_image));
    }

    protected function cardImageUrl(): Attribute
    {
        return Attribute::get(fn () => static::publicUrl($this->card_image));
    }

    /**
     * Only posts visitors may see: published, with a publish date that has passed.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', static::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Newest first; drafts (no publish date) after published posts, newest created first.
     *
     * @param  Builder<Post>  $query
     */
    public function scopeNewestFirst(Builder $query): void
    {
        $query->orderByRaw('published_at is null')->orderByDesc('published_at')->orderByDesc('id');
    }
}
