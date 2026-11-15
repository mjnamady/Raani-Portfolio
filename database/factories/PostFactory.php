<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state: a draft.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => null,
            'title' => ucfirst(fake()->unique()->words(4, true)),
            'slug' => null,
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'featured_image_alt' => null,
            'author_name' => fake()->name(),
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
            'allow_comments' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    /**
     * Theme demo images, so published posts render complete cards without uploads.
     */
    public function withThemeImages(): static
    {
        return $this->afterMaking(function (Post $post) {
            $post->featured_image = Post::THEME_IMAGE_PREFIX.'blog/details/01.png';
            $post->card_image = Post::THEME_IMAGE_PREFIX.'blog/blog-img-1.jpg';
        });
    }
}
