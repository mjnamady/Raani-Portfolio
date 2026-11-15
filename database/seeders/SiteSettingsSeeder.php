<?php

namespace Database\Seeders;

use App\Models\BannerSection;
use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Site identity, menu panel and footer settings. Social links start empty (hidden) and the theme
     * images stay as defaults until the owner uploads their own. Existing values are never overwritten.
     */
    public function run(): void
    {
        $values = SiteSettings::defaults();

        // The menu panel text starts as the banner biography, so no theme filler is shown.
        $biography = BannerSection::query()->value('biography');
        $values['offcanvas.bio'] = is_string($biography) ? mb_substr($biography, 0, 300) : '';

        foreach ($values as $key => $value) {
            if (! Setting::query()->where('key', $key)->exists()) {
                Setting::put($key, $value);
            }
        }

        // DatabaseSeeder disables model events, so clear the cache explicitly.
        Setting::forgetCache();
    }
}
