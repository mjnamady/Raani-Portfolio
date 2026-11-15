<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state: a pending visitor comment.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->published()->withThemeImages(),
            'parent_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'body' => fake()->sentence(18),
            'is_owner' => false,
            'status' => Comment::STATUS_PENDING,
            'approved_at' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => Comment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    public function replyTo(Comment $parent): static
    {
        return $this->state(fn () => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
        ]);
    }

    public function byOwner(): static
    {
        return $this->approved()->state(fn () => ['is_owner' => true]);
    }
}
