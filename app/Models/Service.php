<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'slug', 'icon', 'project_count', 'project_count_label',
    'excerpt', 'content', 'image', 'is_featured', 'sort_order', 'is_active',
])]
class Service extends Model
{
    use HasFactory;

    /**
     * Versioned cache key for the home page cards (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.services.v1';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'project_count' => 'integer',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: give new services a unique slug, and clear the home page cache on change.
     *
     * The slug is kept when the title changes so future detail-page URLs stay stable.
     */
    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if (blank($service->slug)) {
                $service->slug = static::uniqueSlug($service->title, $service->id);
            }
        });

        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * A slug based on the title that no other service uses (ADM-7).
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        // Room for a "-2" style suffix within the 100-character column.
        $base = Str::limit(Str::slug($title), 90, '') ?: 'service';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Active, featured services for the home page cards, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Service>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->featured()
            ->ordered()
            ->get(['id', 'title', 'slug', 'icon', 'project_count', 'project_count_label', 'sort_order', 'is_active', 'is_featured'])
            ->map(fn (Service $service) => $service->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
