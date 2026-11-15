<?php

namespace Database\Factories;

use App\Models\LatestService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LatestService>
 */
class LatestServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(16),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * An item switched off in the admin panel.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
