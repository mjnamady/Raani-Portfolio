<?php

namespace Database\Factories;

use App\Models\ResumeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResumeEntry>
 */
class ResumeEntryFactory extends Factory
{
    /**
     * Define the model's default state: an education entry.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->numberBetween(2000, 2018);

        return [
            'type' => ResumeEntry::TYPE_EDUCATION,
            'title' => fake()->jobTitle(),
            'organisation' => fake()->company().' University',
            'role' => null,
            'period' => $start.'-'.($start + 4),
            'description' => fake()->sentence(18),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function education(): static
    {
        return $this->state(fn () => ['type' => ResumeEntry::TYPE_EDUCATION]);
    }

    public function experience(): static
    {
        return $this->state(fn () => [
            'type' => ResumeEntry::TYPE_EXPERIENCE,
            'title' => null,
            'organisation' => fake()->company(),
            'role' => fake()->jobTitle(),
            'period' => fake()->numberBetween(2015, 2022).' – Present',
        ]);
    }

    /**
     * An entry switched off in the admin panel.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
