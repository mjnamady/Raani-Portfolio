<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'logo' => Client::THEME_LOGO_DIRECTORY.'company-logo-'.fake()->numberBetween(1, 8).'.svg',
            'url' => fake()->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * A logo switched off in the admin panel.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
