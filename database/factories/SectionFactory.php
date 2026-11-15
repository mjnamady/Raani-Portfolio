<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'home.'.fake()->unique()->slug(2),
            'eyebrow' => fake()->words(2, true),
            'title' => fake()->sentence(4),
            'subtitle' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * A section switched off in the admin panel.
     */
    public function hidden(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
