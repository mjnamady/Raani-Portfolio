<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Stat;
use Illuminate\Database\Seeder;

class CounterAreaSeeder extends Seeder
{
    /**
     * Recreate the theme's Counter Area content. Existing content is never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_COUNTER], ['is_active' => true]);

        if (! Stat::query()->where('placement', Stat::PLACEMENT_HIGHLIGHT)->exists()) {
            Stat::create([
                'value' => 25,
                'suffix' => null,
                'label' => "Years Of\nexperience",
                'description' => 'Business consulting consultants provide expert advice and guidance to help businesses improve their performance and efficiency.',
                'placement' => Stat::PLACEMENT_HIGHLIGHT,
                'sort_order' => 1,
                'is_active' => true,
            ]);
        }

        if (! Stat::query()->where('placement', Stat::PLACEMENT_COUNTER)->exists()) {
            foreach ([
                ['value' => 20, 'suffix' => 'k+', 'label' => 'Our Project Complete'],
                ['value' => 10, 'suffix' => 'k+', 'label' => 'Our Natural Products'],
                ['value' => 200, 'suffix' => '+', 'label' => 'Clients Reviews'],
                ['value' => 1000, 'suffix' => '+', 'label' => 'Our Satisfied Clients'],
            ] as $order => $stat) {
                Stat::create($stat + [
                    'placement' => Stat::PLACEMENT_COUNTER,
                    'sort_order' => $order + 1,
                    'is_active' => true,
                ]);
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Stat::forgetCounterCache();
    }
}
