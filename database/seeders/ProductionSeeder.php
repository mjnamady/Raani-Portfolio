<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * What a live site starts with: the admin account (from ADMIN_* variables) and the site
     * settings. No theme demo projects, posts, comments or contact details: the owner adds their own.
     * Safe to run on every deploy: each seeder only fills what is missing and never overwrites.
     */
    public function run(): void
    {
        $this->call([
            AdminDataSeeder::class,
            SiteSettingsSeeder::class,
        ]);
    }
}
