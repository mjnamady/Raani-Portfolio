<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state: a draft.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => null,
            'title' => ucfirst(fake()->unique()->words(3, true)),
            'slug' => null,
            'tagline' => fake()->jobTitle(),
            'client_name' => fake()->name(),
            'project_date' => fake()->dateTimeBetween('-3 years')->format('Y-m-d'),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'features' => ['Research', 'Design', 'Development'],
            'external_url' => null,
            'is_featured' => false,
            'status' => Project::STATUS_DRAFT,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => Project::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
