<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state: an unread message from the contact form.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => Message::SOURCE_CONTACT,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }

    public function chat(): static
    {
        return $this->state(fn () => ['source' => Message::SOURCE_CHAT, 'subject' => null]);
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }

    public function replied(): static
    {
        return $this->state(fn () => ['read_at' => now(), 'replied_at' => now()]);
    }
}
