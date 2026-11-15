<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Fillable(['icon', 'title', 'done_count', 'done_label', 'description', 'is_active'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * Versioned cache key for the home page rows (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.skills.v1';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'done_count' => 'integer',
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached home page rows.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Visible skills for the home page, in order, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return Collection<int, Skill>
     */
    public static function forHomePage(): Collection
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (Skill $skill) => $skill->getAttributes())
            ->all());

        return static::hydrate($rows);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Make this the only highlighted skill.
     */
    public function highlight(): void
    {
        DB::transaction(function () {
            static::query()->whereKeyNot($this->getKey())->where('is_highlighted', true)->update(['is_highlighted' => false]);

            $this->is_highlighted = true;
            $this->save();
        });

        // The mass update above fires no model events.
        static::forgetCache();
    }

    /**
     * Keep exactly one visible skill highlighted: a hidden skill loses the highlight, and when
     * no visible skill has it, the first visible one gets it (SKL-2). The theme's sliding
     * background always needs one "current" row.
     */
    public static function ensureOneHighlighted(): void
    {
        DB::transaction(function () {
            static::query()->where('is_active', false)->where('is_highlighted', true)->update(['is_highlighted' => false]);

            $highlighted = static::query()->active()->where('is_highlighted', true)->ordered()->get();

            if ($highlighted->count() > 1) {
                static::query()->whereKey($highlighted->skip(1)->modelKeys())->update(['is_highlighted' => false]);
            }

            if ($highlighted->isEmpty()) {
                static::query()->active()->ordered()->first()?->forceFill(['is_highlighted' => true])->saveQuietly();
            }
        });

        static::forgetCache();
    }

    /**
     * @param  Builder<Skill>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Skill>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
