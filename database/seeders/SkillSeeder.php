<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Recreate the theme's My Skill content. Existing content is never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_SKILLS], [
            'eyebrow' => 'My Skill',
            'title' => "Elevated Designs Personalized\nthe best Experiences",
            'is_active' => true,
        ]);

        if (! Skill::query()->exists()) {
            foreach ([
                ['icon' => 'fa-light fa-building-columns', 'title' => 'UI / Visual Design', 'done_count' => 21, 'description' => 'My work is driven by the belief that thoughtful design and strategic planning can empower brands and transform businesses.'],
                ['icon' => 'fa-light fa-calendar', 'title' => 'Product Planning', 'done_count' => 21, 'description' => 'In this portfolio, you will find a curated selection of projects that highlight my skills in responsive web design.'],
                ['icon' => 'fa-light fa-pen-nib', 'title' => 'Motion Design', 'done_count' => 20, 'description' => 'Each project here showcases my commitment to excellence and adaptability, tailored to meet each client’s unique needs.'],
            ] as $position => $attributes) {
                $skill = new Skill($attributes + ['done_label' => 'Done', 'is_active' => true]);
                $skill->is_highlighted = $position === 0;
                $skill->sort_order = $position + 1;
                $skill->save();
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Skill::forgetCache();
    }
}
