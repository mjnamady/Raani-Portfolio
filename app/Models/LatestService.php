<?php

namespace App\Models;

use Database\Factories\LatestServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['title', 'description', 'sort_order', 'is_active'])]
class LatestService extends Model
{
    /** @use HasFactory<LatestServiceFactory> */
    use HasFactory;

    /**
     * Versioned cache key for the home page list (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.latest_services.v1';

    /**
     * Theme portrait shown next to the list until an image is uploaded.
     */
    public const DEFAULT_IMAGE = 'frontend/assets/images/services/latest-services-user-image-two.png';

    /**
     * Size uploaded side images are cropped to (same ratio as the theme's 567x588 portrait).
     */
    public const IMAGE_WIDTH = 640;

    public const IMAGE_HEIGHT = 664;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached home page list.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Active items for the home "Latest Service" section (HOME-3), cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, LatestService>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (LatestService $item) => $item->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * @param  Builder<LatestService>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<LatestService>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
