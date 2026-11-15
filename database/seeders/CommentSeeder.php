<?php

namespace Database\Seeders;

use App\Models\BannerSection;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Demo comments on the newest published post, using the theme's names: two approved
     * comments, an owner reply and one comment waiting for approval. Existing comments are never touched.
     */
    public function run(): void
    {
        $post = Post::query()->published()->newestFirst()->first();

        if (! $post || Comment::query()->exists()) {
            return;
        }

        $first = $this->comment($post, 'Stanio lainto', 'stanio@example.com', 'Really useful breakdown. I rebuilt my own portfolio around the "three to five best projects" idea and it already feels much clearer.', true, 3);
        $this->comment($post, BannerSection::query()->value('name') ?? 'Brooklyn Simmons', 'owner@example.com', 'Thank you, Stanio! That one change makes the biggest difference. Good luck with the new version.', true, 2, $first, isOwner: true);
        $this->comment($post, 'Court Henry', 'court@example.com', "Loved the quote in the middle.\nDo you have tips for writing the short story for each project?", true, 1);
        $this->comment($post, 'Jenny Wilson', 'jenny@example.com', 'Great post! Waiting for the next one.', false, 0);
    }

    protected function comment(Post $post, string $name, string $email, string $body, bool $approved, int $daysAgo, ?Comment $parent = null, bool $isOwner = false): Comment
    {
        $comment = new Comment(['author_name' => $name, 'author_email' => $email, 'body' => $body]);
        $comment->post_id = $post->id;
        $comment->parent_id = $parent?->id;
        $comment->is_owner = $isOwner;
        $comment->setApproved($approved);
        $comment->created_at = now()->subDays($daysAgo);
        $comment->save();

        return $comment;
    }
}
