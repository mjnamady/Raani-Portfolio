<?php

namespace Database\Factories;

use App\Models\SkillBar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SkillBar>
 */
class SkillBarFactory extends Factory
{
    /**
     * Define the model's default state: a visible design bar.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => SkillBar::GROUP_DESIGN,
            'name' => ucfirst(fake()->unique()->word()),
            'percent' => fake()->numberBetween(40, 100),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function development(): static
    {
        return $this->state(fn () => ['group' => SkillBar::GROUP_DEVELOPMENT]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
