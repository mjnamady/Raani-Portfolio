<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['quote', 'name', 'role', 'is_active'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    /**
     * Versioned cache key for the home page slides (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.testimonials.v1';

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
     * The model's boot hooks: any change clears the cached home page slides.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Visible testimonials for the home page, in order, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Testimonial>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Testimonial $testimonial) => $testimonial->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
