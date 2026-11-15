<?php

namespace App\Models;

use App\Support\RichText;
use Database\Factories\ProjectFactory;
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
    'category_id', 'title', 'slug', 'tagline', 'client_name', 'project_date',
    'card_image_alt', 'cover_image_alt', 'content', 'features', 'external_url',
    'is_featured', 'status', 'published_at', 'meta_title', 'meta_description',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /**
     * Versioned cache key for the home "Latest Portfolio" cards (stored as plain arrays).
     */
    public const CACHE_KEY = 'home.portfolio.v1';

    /**
     * How many projects the home "Latest Portfolio" section shows.
     */
    public const HOME_LIMIT = 4;

    /**
     * Theme demo images live under this folder; they are read-only and never deleted.
     */
    public const THEME_IMAGE_PREFIX = 'frontend/assets/images/';

    /**
     * Card image size (home and projects page), the theme's 1920x1572 ratio at half size.
     */
    public const CARD_WIDTH = 960;

    public const CARD_HEIGHT = 786;

    /**
     * Cover image size on the details page (the theme's 1290x560 thumbnail).
     */
    public const COVER_WIDTH = 1290;

    public const COVER_HEIGHT = 560;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'project_date' => 'date',
            'published_at' => 'datetime',
            'features' => 'array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The model's boot hooks: unique slug, sanitized content, a publish date for published
     * projects, and cache clearing on change.
     */
    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            $project->slug = static::uniqueSlug($project->slug ?: $project->title, $project->id);

            // Defence in depth: content is sanitized whatever saved it (admin form, seeder, ...).
            if ($project->isDirty('content')) {
                $project->content = RichText::clean($project->content);
            }

            if ($project->status === static::STATUS_PUBLISHED && $project->published_at === null) {
                $project->published_at = now();
            }
        });

        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * A slug based on $text that no other project uses (ADM-7).
     */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($text), 130, '') ?: 'project';
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
     * Up to four featured, published projects for the home page, or the latest published
     * ones when none are featured; cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Project>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, function () {
            $featured = static::query()->published()->where('is_featured', true)->ordered()->limit(static::HOME_LIMIT)->get();

            $projects = $featured->isNotEmpty()
                ? $featured
                : static::query()->published()->latest('published_at')->limit(static::HOME_LIMIT)->get();

            return $projects->map(fn (Project $project) => $project->getAttributes())->all();
        });

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
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    /**
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    protected function cardImageUrl(): Attribute
    {
        return Attribute::get(fn () => static::publicUrl($this->card_image));
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn () => static::publicUrl($this->cover_image));
    }

    /**
     * Only projects visitors may see: published, with a publish date that has passed.
     *
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', static::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Admin order: position first, newest first among equal positions.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }
}
