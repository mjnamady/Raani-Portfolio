<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'icon' => 'fa-light '.fake()->randomElement(['fa-pen-ruler', 'fa-bezier-curve', 'fa-lightbulb', 'fa-envelope']),
            'project_count' => fake()->numberBetween(10, 400),
            'project_count_label' => 'Projects',
            'is_featured' => true,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the service is hidden from the site.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
