<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => 'fa-light '.fake()->randomElement(['fa-pen-nib', 'fa-calendar', 'fa-code', 'fa-palette']),
            'title' => ucfirst(fake()->unique()->words(2, true)),
            'done_count' => fake()->numberBetween(5, 60),
            'done_label' => 'Done',
            'description' => fake()->sentence(16),
            'is_highlighted' => false,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function highlighted(): static
    {
        return $this->state(fn () => ['is_highlighted' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
