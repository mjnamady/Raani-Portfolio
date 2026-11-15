<?php

namespace Database\Seeders;

use App\Models\BannerSection;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Support\RichText;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Recreate the theme's demo posts (images served from the theme folder).
     * Existing posts are never overwritten.
     */
    public function run(): void
    {
        $this->call(PostCategorySeeder::class);

        Section::query()->firstOrCreate(['key' => Section::HOME_BLOG], [
            'eyebrow' => 'Blog and news',
            'title' => "Elevating Personal Branding the\nthrough Powerful Portfolios",
            'is_active' => true,
        ]);

        if (! Post::query()->exists()) {
            $categories = PostCategory::query()->pluck('id', 'name');
            $author = BannerSection::query()->value('name') ?? 'Brooklyn Simmons';
            $theme = Post::THEME_IMAGE_PREFIX.'blog/';

            foreach ($this->demoPosts() as $position => $demo) {
                $post = new Post([
                    'category_id' => $categories[$demo['category']] ?? null,
                    'title' => $demo['title'],
                    // Model events are off under DatabaseSeeder: slug and sanitizing are done here.
                    'slug' => Post::uniqueSlug($demo['title']),
                    'excerpt' => 'How a focused portfolio turns visitors into clients: structure, story and proof of work.',
                    'body' => RichText::clean($this->demoBody()),
                    'featured_image_alt' => $demo['title'],
                    'author_name' => $author,
                    'status' => Post::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(($position + 1) * 6),
                ]);
                $post->featured_image = $theme.'details/01.png';
                $post->card_image = $theme.$demo['image'];
                $post->save();

                $post->tags()->sync(
                    collect(Tag::idsForNames($demo['tags']))->mapWithKeys(fn (int $id, int $order) => [$id => ['sort_order' => $order]])->all()
                );
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Post::forgetCache();
    }

    /**
     * @return list<array{title: string, category: string, image: string, tags: list<string>}>
     */
    protected function demoPosts(): array
    {
        return [
            ['title' => 'Let’s Bring Your Ideas to Life', 'category' => 'Branding', 'image' => 'blog-img-4.jpg', 'tags' => ['UX Design', 'Graphics']],
            ['title' => 'Inspiring the World, One Project at a Time', 'category' => 'Web Design', 'image' => 'blog-img-5.jpg', 'tags' => ['Web Design']],
            ['title' => 'Each Project Showcases My Approach and Dedication', 'category' => 'UX Design', 'image' => 'blog-img-6.jpg', 'tags' => ['UX Design', 'Web Design']],
            ['title' => 'Elevate Your Brand With a Stunning Portfolio', 'category' => 'Branding', 'image' => 'blog-img-1.jpg', 'tags' => ['Graphics', 'Branding']],
            ['title' => 'Showcase Your Talent With a Clear Case Study', 'category' => 'UX Design', 'image' => 'blog-img-2.jpg', 'tags' => ['UX Design']],
            ['title' => 'Designing Websites That Convert Visitors', 'category' => 'Web Design', 'image' => 'blog-img-3.jpg', 'tags' => ['Web Design', 'Graphics']],
        ];
    }

    protected function demoBody(): string
    {
        return '<p>A portfolio is often the first conversation you have with a future client. '
            .'It should tell them, in a few seconds, what you do, who you do it for and why it works.</p>'
            .'<p>Start with your strongest work, explain the problem each project solved, and show the result in numbers where you can.</p>'
            .'<blockquote>Good design is obvious. Great design is transparent.</blockquote>'
            .'<h3>Showcase your talent with a clear structure</h3>'
            .'<ul><li>Lead with three to five of your best projects.</li><li>Write a short story for each one: the brief, your approach, the outcome.</li><li>Finish with a simple way to get in touch.</li></ul>'
            .'<p>Keep it current: a portfolio that is updated regularly shows you are active, curious and ready for the next project.</p>';
    }
}
