<?php

namespace App\Models;

use Database\Factories\ResumeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['title', 'organisation', 'role', 'period', 'description', 'is_active'])]
class ResumeEntry extends Model
{
    /** @use HasFactory<ResumeEntryFactory> */
    use HasFactory;

    public const TYPE_EDUCATION = 'education';

    public const TYPE_EXPERIENCE = 'experience';

    /**
     * Versioned cache key for the home page entries (stored as plain arrays, see forHomePage()).
     */
    public const CACHE_KEY = 'home.resume.v1';

    /**
     * Theme photo shown next to the experience list until an image is uploaded.
     */
    public const DEFAULT_EXPERIENCE_IMAGE = 'frontend/assets/images/experiences/expert-img-two.jpg';

    /**
     * Size uploaded experience images are cropped to (the theme photo's size).
     */
    public const IMAGE_WIDTH = 630;

    public const IMAGE_HEIGHT = 479;

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
     * The model's boot hooks: any change clears the cached home page entries.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * Active entries of both types for the home page, cached until the next change.
     *
     * Plain attribute arrays are cached, not models: config/cache.php sets
     * serializable_classes to false, so cached objects would come back incomplete.
     *
     * @return array{education: Collection<int, ResumeEntry>, experience: Collection<int, ResumeEntry>}
     */
    public static function forHomePage(): array
    {
        $rows = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (ResumeEntry $entry) => $entry->getAttributes())
            ->all());

        $entries = static::hydrate($rows);

        return [
            'education' => $entries->where('type', static::TYPE_EDUCATION)->values(),
            'experience' => $entries->where('type', static::TYPE_EXPERIENCE)->values(),
        ];
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * The education card's sub-title, followed by the institution when there is one.
     */
    public function educationHeading(): string
    {
        return collect([$this->title, $this->organisation])->filter()->implode(' · ');
    }

    /**
     * The small label above an experience entry.
     */
    public function experienceLabel(): string
    {
        return $this->period ?: 'Experience';
    }

    /**
     * @param  Builder<ResumeEntry>  $query
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * @param  Builder<ResumeEntry>  $query
     */
    public function scopeEducation(Builder $query): void
    {
        $query->where('type', static::TYPE_EDUCATION);
    }

    /**
     * @param  Builder<ResumeEntry>  $query
     */
    public function scopeExperience(Builder $query): void
    {
        $query->where('type', static::TYPE_EXPERIENCE);
    }

    /**
     * @param  Builder<ResumeEntry>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<ResumeEntry>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
