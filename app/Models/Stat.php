<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['value', 'suffix', 'label', 'description', 'placement', 'sort_order', 'is_active'])]
class Stat extends Model
{
    use HasFactory;

    public const PLACEMENT_BANNER = 'banner';

    /**
     * The large "25 Years of experience" block of the home Counter Area.
     */
    public const PLACEMENT_HIGHLIGHT = 'highlight';

    /**
     * The stat cards next to the highlight in the home Counter Area.
     */
    public const PLACEMENT_COUNTER = 'counter';

    /**
     * Versioned cache key for the home Counter Area (stored as plain arrays, see forCounterArea()).
     */
    public const COUNTER_CACHE_KEY = 'home.counter.v1';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached home page sections.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetHomeCaches());
        static::deleted(fn () => static::forgetHomeCaches());
    }

    /**
     * Highlight and stat cards for the home Counter Area (HOME-5), cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return array{highlight: ?Stat, counters: Collection<int, Stat>}
     */
    public static function forCounterArea(): array
    {
        $cached = Cache::rememberForever(static::COUNTER_CACHE_KEY, fn () => [
            'highlight' => static::query()->forPlacement(static::PLACEMENT_HIGHLIGHT)->active()->first()?->getAttributes(),
            'counters' => static::query()->forPlacement(static::PLACEMENT_COUNTER)->active()->get()
                ->map(fn (Stat $stat) => $stat->getAttributes())
                ->all(),
        ]);

        return [
            'highlight' => $cached['highlight'] ? static::hydrate([$cached['highlight']])->first() : null,
            'counters' => static::hydrate($cached['counters']),
        ];
    }

    public static function forgetCounterCache(): void
    {
        Cache::forget(static::COUNTER_CACHE_KEY);
    }

    /**
     * Clear every cached home page section that shows stats.
     */
    public static function forgetHomeCaches(): void
    {
        BannerSection::forgetCache();
        static::forgetCounterCache();
    }

    /**
     * The label split into lines, so the owner can control where it wraps.
     *
     * @return list<string>
     */
    public function labelLines(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $this->label)),
            fn (string $line) => $line !== '',
        ));
    }

    /**
     * Stats shown in the given placement, in display order.
     *
     * @param  Builder<Stat>  $query
     */
    public function scopeForPlacement(Builder $query, string $placement): void
    {
        $query->where('placement', $placement)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Only stats switched on in the admin panel.
     *
     * @param  Builder<Stat>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
