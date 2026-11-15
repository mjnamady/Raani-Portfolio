<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminDataSeeder::class,
            BannerSectionSeeder::class,
            ServiceSeeder::class,
            CounterAreaSeeder::class,
            LatestServicesSeeder::class,
            ResumeEntrySeeder::class,
            ClientSeeder::class,
            ProjectSeeder::class,
            SkillSeeder::class,
            TestimonialSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
            AboutPageSeeder::class,
            ContactSettingsSeeder::class,
            SiteSettingsSeeder::class,
        ]);
    }
}
