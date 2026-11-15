<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A site-wide setting (PRD §6): a dotted key such as "contact.emails" with a JSON value.
 * The group is the part before the first dot ("contact").
 */
#[Fillable(['key', 'value', 'group'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * Versioned cache key for every setting, stored as plain decoded values keyed by setting key.
     */
    public const CACHE_KEY = 'settings.v1';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached settings, once the transaction has
     * committed (straight away outside one), so a request in between can not cache the old rows.
     */
    protected static function booted(): void
    {
        static::saved(fn () => DB::afterCommit(fn () => static::forgetCache()));
        static::deleted(fn () => DB::afterCommit(fn () => static::forgetCache()));
    }

    /**
     * Every setting from the cache, keyed by setting key.
     *
     * @return array<string, mixed>
     */
    public static function allCached(): array
    {
        // memo(): read the cache store once per request; every page reads settings many times.
        return Cache::memo()->rememberForever(static::CACHE_KEY, fn () => static::query()
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all());
    }

    /**
     * One setting's value, or the default when it has not been saved yet.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allCached()[$key] ?? $default;
    }

    /**
     * Every saved setting in a group, keyed by the part after the group ("emails" for "contact.emails").
     *
     * @return array<string, mixed>
     */
    public static function group(string $group): array
    {
        return collect(static::allCached())
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, $group.'.'))
            ->mapWithKeys(fn (mixed $value, string $key) => [Str::after($key, $group.'.') => $value])
            ->all();
    }

    /**
     * Save one setting.
     */
    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => Str::before($key, '.')]);
    }

    /**
     * Clear the cached settings, both the request's memo and the cache store.
     */
    public static function forgetCache(): void
    {
        Cache::memo()->forget(static::CACHE_KEY);
    }
}
