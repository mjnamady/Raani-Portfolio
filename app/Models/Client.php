<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'url', 'is_active'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * Versioned cache key for the home page logos (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.clients.v1';

    /**
     * Demo logos shipped with the theme live here; they are read-only and never deleted.
     */
    public const THEME_LOGO_DIRECTORY = 'frontend/assets/images/our-supported-company/';

    /**
     * Box uploaded logos are scaled down to fit (the theme logos are 184x60).
     */
    public const LOGO_MAX_WIDTH = 400;

    public const LOGO_MAX_HEIGHT = 160;

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
     * The model's boot hooks: any change clears the cached home page logos.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Active logos for the home page (HOME-8), cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Client>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Client $client) => $client->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Whether the logo is one of the theme's demo files rather than an upload.
     */
    public function hasThemeLogo(): bool
    {
        return Str::startsWith((string) $this->logo, static::THEME_LOGO_DIRECTORY);
    }

    /**
     * Public URL of the logo: a theme demo file or an upload on the public disk.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->hasThemeLogo()
            ? asset($this->logo)
            : Storage::disk('public')->url($this->logo));
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Client>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
