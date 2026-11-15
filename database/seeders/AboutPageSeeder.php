<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\SkillBar;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * The About page's skill columns. Existing content is never overwritten. The "Get in touch"
     * text is seeded by ContactSettingsSeeder.
     */
    public function run(): void
    {
        foreach (SkillBar::GROUPS as $group) {
            Section::query()->firstOrCreate(['key' => $group['section']], ['title' => $group['title'], 'is_active' => true]);
        }

        if (! SkillBar::query()->exists()) {
            $bars = [
                // The theme spells it "PHOTOSHOT" and repeats the design list in the development column.
                SkillBar::GROUP_DESIGN => ['Photoshop' => 100, 'Figma' => 95, 'Adobe XD' => 60, 'Adobe Illustrator' => 70],
                SkillBar::GROUP_DEVELOPMENT => ['HTML & CSS' => 95, 'JavaScript' => 85, 'PHP / Laravel' => 80, 'WordPress' => 70],
            ];

            foreach ($bars as $group => $skills) {
                $position = 0;

                foreach ($skills as $name => $percent) {
                    $bar = new SkillBar(['name' => $name, 'percent' => $percent, 'is_active' => true]);
                    $bar->group = $group;
                    $bar->sort_order = ++$position;
                    $bar->save();
                }
            }
        }

        // DatabaseSeeder disables model events, so clear the caches explicitly.
        Section::forgetCache();
        SkillBar::forgetCache();
    }
}
