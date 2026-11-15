<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'greeting', 'name', 'role',
    'biography_heading', 'biography',
    'contact_heading',
    'image_alt', 'is_active',
])]
class BannerSection extends Model
{
    use HasFactory;

    /**
     * Versioned so entries cached as serialized models by earlier code are never read back.
     */
    public const CACHE_KEY = 'home.banner.v2';

    /**
     * Theme portrait shown until an image is uploaded.
     */
    public const DEFAULT_IMAGE = 'frontend/assets/images/banner/banner-user-image-five.png';

    /**
     * Size uploaded portraits are cropped to (same ratio as the theme's 459x579 portrait).
     */
    public const IMAGE_WIDTH = 640;

    public const IMAGE_HEIGHT = 808;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The model's boot hooks: saving the banner clears the cached copy used by the home page.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    /**
     * The banner row, created on first use so the admin form always has something to edit.
     */
    public static function current(): self
    {
        return static::query()->first() ?? static::create(static::defaults());
    }

    /**
     * Banner and its active stats for the public home page, cached until the next save.
     *
     * @return array{banner: ?BannerSection, stats: Collection<int, Stat>}
     */
    public static function forHomePage(): array
    {
        // Cache plain attribute arrays, not models: config/cache.php sets serializable_classes
        // to false, so cached objects would come back as __PHP_Incomplete_Class.
        $cached = Cache::rememberForever(static::CACHE_KEY, fn () => [
            'banner' => static::query()->first()?->getAttributes(),
            'stats' => Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->active()->get()
                ->map(fn (Stat $stat) => $stat->getAttributes())
                ->all(),
        ]);

        return [
            'banner' => $cached['banner'] ? static::hydrate([$cached['banner']])->first() : null,
            'stats' => Stat::hydrate($cached['stats']),
        ];
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * The theme's demo content, used for the first row and by the seeder.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'greeting' => 'Hi, I am',
            'name' => 'Brooklyn Simmons',
            'role' => 'a Product Designer',
            'biography_heading' => 'Biography :',
            'biography' => "I'm a freelance product designer & developer based in London. I’m very passionate about the work I do.",
            // The address, email and phone below it come from the contact settings (ContactDetails).
            'contact_heading' => 'Contact :',
            'image_alt' => 'Portrait of Brooklyn Simmons',
            'is_active' => true,
        ];
    }

    /**
     * Public URL of the portrait, or the theme image when none is uploaded.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image
            ? Storage::disk('public')->url($this->image)
            : asset(self::DEFAULT_IMAGE));
    }
}
