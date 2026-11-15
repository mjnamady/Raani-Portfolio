<?php

namespace Database\Seeders;

use App\Models\LatestService;
use App\Models\Section;
use Illuminate\Database\Seeder;

class LatestServicesSeeder extends Seeder
{
    /**
     * Recreate the theme's Latest Service content. Existing content is never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_LATEST_SERVICES], [
            'eyebrow' => 'Latest Service',
            'title' => 'Inspiring The World One Project',
            'subtitle' => 'Business consulting consultants provide expert advice and guidance to help businesses improve their performance, efficiency and organisation.',
            'image_alt' => 'Portrait next to the latest services list',
            'is_active' => true,
        ]);

        if (! LatestService::query()->exists()) {
            foreach ([
                ['title' => 'A Portfolio of Creativity', 'description' => 'Business consulting consultants provide expert advice and guidance to help businesses improve their performance and efficiency.'],
                ['title' => 'My Portfolio of Innovation', 'description' => 'My work is driven by the belief that thoughtful design and strategic planning can empower brands and transform businesses.'],
                ['title' => 'A Showcase of My Projects', 'description' => 'In this portfolio, you will find a curated selection of projects that highlight my skills in responsive web design.'],
            ] as $order => $item) {
                LatestService::create($item + [
                    'sort_order' => $order + 1,
                    'is_active' => true,
                ]);
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        LatestService::forgetCache();
    }
}
