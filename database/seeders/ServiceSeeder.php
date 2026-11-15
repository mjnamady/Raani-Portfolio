<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Recreate the theme's four service cards. Existing services are never overwritten.
     */
    public function run(): void
    {
        if (Service::query()->exists()) {
            return;
        }

        foreach ([
            ['title' => 'Web Design', 'icon' => 'fa-light fa-pen-ruler', 'project_count' => 120],
            ['title' => 'Ui/Ux Design', 'icon' => 'fa-light fa-bezier-curve', 'project_count' => 241],
            ['title' => 'Web Research', 'icon' => 'fa-light fa-lightbulb', 'project_count' => 240],
            ['title' => 'Marketing', 'icon' => 'fa-light fa-envelope', 'project_count' => 331],
        ] as $order => $service) {
            Service::create($service + [
                'slug' => Service::uniqueSlug($service['title']),
                'project_count_label' => 'Projects',
                'is_featured' => true,
                'sort_order' => $order + 1,
                'is_active' => true,
            ]);
        }

        // DatabaseSeeder disables model events, so clear the home page cache explicitly.
        Service::forgetCache();
    }
}
