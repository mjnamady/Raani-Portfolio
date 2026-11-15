<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageReply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageReply>
 */
class MessageReplyFactory extends Factory
{
    /**
     * Define the model's default state: a reply that is still being sent.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'subject' => 'Re: '.fake()->sentence(3),
            'body' => fake()->paragraph(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['sent_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['failed_at' => now(), 'error' => 'Connection could not be established']);
    }
}
