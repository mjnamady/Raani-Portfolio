<?php

namespace Database\Seeders;

use App\Models\BannerSection;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class BannerSectionSeeder extends Seeder
{
    /**
     * Recreate the theme's banner content. Existing content is never overwritten.
     */
    public function run(): void
    {
        if (! BannerSection::query()->exists()) {
            BannerSection::create(BannerSection::defaults());
        }

        if (! Stat::query()->where('placement', Stat::PLACEMENT_BANNER)->exists()) {
            foreach ([
                ['value' => 40, 'suffix' => 'k', 'label' => 'Winning award'],
                ['value' => 100, 'suffix' => 'k', 'label' => 'Complete project'],
                ['value' => 800, 'suffix' => '+', 'label' => 'Client review'],
            ] as $order => $stat) {
                Stat::create($stat + [
                    'placement' => Stat::PLACEMENT_BANNER,
                    'sort_order' => $order + 1,
                    'is_active' => true,
                ]);
            }
        }

        // DatabaseSeeder disables model events, so clear the home page cache explicitly.
        BannerSection::forgetCache();
    }
}
