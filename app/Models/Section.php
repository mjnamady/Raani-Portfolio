<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'eyebrow', 'title', 'subtitle', 'image_alt', 'is_active', 'sort_order'])]
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /**
     * Versioned cache key for every section, stored as plain arrays keyed by section key.
     */
    public const CACHE_KEY = 'sections.v1';

    public const HOME_COUNTER = 'home.counter';

    public const HOME_LATEST_SERVICES = 'home.latest_services';

    public const HOME_EDUCATION = 'home.education';

    public const HOME_EXPERIENCE = 'home.experience';

    public const HOME_CLIENTS = 'home.clients';

    public const HOME_PORTFOLIO = 'home.portfolio';

    public const HOME_SKILLS = 'home.skills';

    public const HOME_TESTIMONIALS = 'home.testimonials';

    public const HOME_BLOG = 'home.blog';

    public const ABOUT_SKILLS_DESIGN = 'about.skills_design';

    public const ABOUT_SKILLS_DEVELOPMENT = 'about.skills_development';

    /**
     * The "Get in touch" heading and text next to the contact form (shared by the pages that show it).
     */
    public const CONTACT_GET_IN_TOUCH = 'contact.get_in_touch';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The model's boot hooks: any change clears the cached sections, once the transaction has
     * committed (straight away outside one), so a request in between can not cache the old rows.
     */
    protected static function booted(): void
    {
        static::saved(fn () => DB::afterCommit(fn () => static::forgetCache()));
        static::deleted(fn () => DB::afterCommit(fn () => static::forgetCache()));
    }

    /**
     * The section with the given key, created switched on the first time it is needed.
     */
    public static function forKey(string $key): self
    {
        return static::query()->firstOrCreate(['key' => $key], ['is_active' => true]);
    }

    /**
     * The section with the given key from the cache, or null when it has no row yet.
     */
    public static function cached(string $key): ?self
    {
        // Plain arrays, not models: config/cache.php sets serializable_classes to false.
        $sections = Cache::rememberForever(static::CACHE_KEY, fn () => static::query()
            ->get()
            ->mapWithKeys(fn (Section $section) => [$section->key => $section->getAttributes()])
            ->all());

        return isset($sections[$key]) ? static::hydrate([$sections[$key]])->first() : null;
    }

    /**
     * Whether the section is switched on; sections without a row are shown.
     */
    public static function isVisible(string $key): bool
    {
        return static::cached($key)?->is_active ?? true;
    }

    /**
     * Public URL of the uploaded side image, or the given theme image when none is uploaded.
     */
    public function imageUrl(string $default): string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : asset($default);
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
