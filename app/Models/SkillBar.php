<?php

namespace App\Models;

use Database\Factories\SkillBarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * A progress bar in the About page's "Design Skill" / "Development Skill" columns (ABT-5).
 */
#[Fillable(['name', 'percent', 'is_active'])]
class SkillBar extends Model
{
    /** @use HasFactory<SkillBarFactory> */
    use HasFactory;

    public const GROUP_DESIGN = 'design';

    public const GROUP_DEVELOPMENT = 'development';

    /**
     * Each column, its sections row (title + on/off) and the theme's title until one is saved.
     *
     * @var array<string, array{section: string, title: string}>
     */
    public const GROUPS = [
        self::GROUP_DESIGN => ['section' => Section::ABOUT_SKILLS_DESIGN, 'title' => 'Design Skill'],
        self::GROUP_DEVELOPMENT => ['section' => Section::ABOUT_SKILLS_DEVELOPMENT, 'title' => 'Development Skill'],
    ];

    /**
     * Versioned cache key for the About page bars (stored as plain arrays, see forAboutPage()).
     */
    public const CACHE_KEY = 'about.skill_bars.v1';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percent' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached About page bars.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Visible bars per column, in order, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return array<string, Collection<int, SkillBar>>
     */
    public static function forAboutPage(): array
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (SkillBar $bar) => $bar->getAttributes())
            ->all());

        $bars = static::hydrate($rows);

        return collect(array_keys(static::GROUPS))
            ->mapWithKeys(fn (string $group) => [$group => $bars->where('group', $group)->values()])
            ->all();
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * The percentage kept within 0-100, for the bar width and screen readers.
     */
    public function width(): int
    {
        return max(0, min(100, (int) $this->percent));
    }

    /**
     * @param  Builder<SkillBar>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<SkillBar>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
