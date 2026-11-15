<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Recreate the theme's Testimonial content. Existing content is never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_TESTIMONIALS], [
            'title' => 'Testimonial',
            'is_active' => true,
        ]);

        if (! Testimonial::query()->exists()) {
            // The theme repeats half a sentence ("skilA personal…"); the quote is cleaned up here.
            $quote = "A personal portfolio is a curated collection of an individual's professional work, showcasing their skills, experience and the results they deliver.";

            foreach ([
                ['name' => 'Cameron Williamson', 'role' => 'UI/UX Designer'],
                ['name' => 'Leslie Alexander', 'role' => 'UI/UX Designer'],
                ['name' => 'Jenny Wilson', 'role' => 'Product Manager'],
                ['name' => 'Robert Fox', 'role' => 'Founder'],
            ] as $position => $attributes) {
                $testimonial = new Testimonial($attributes + ['quote' => $quote, 'is_active' => true]);
                $testimonial->sort_order = $position + 1;
                $testimonial->save();
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Testimonial::forgetCache();
    }
}
