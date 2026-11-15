<?php

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\BannerSection;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\PostSeeder;

function publishedPost(array $attributes = []): Post
{
    return Post::factory()->published()->withThemeImages()->create($attributes);
}

/**
 * Only the home "Blog and News" section, so other sections never affect assertions.
 */
function blogNewsHtml(string $html): string
{
    $start = strpos($html, '<section class="blog-and-news-are');

    if ($start === false) {
        return '';
    }

    return substr($html, $start, strpos($html, '</section>', $start) - $start);
}

// ---------- Home: Blog and News ----------

test('the home page shows the 3 latest published posts linking to their pages', function () {
    publishedPost(['title' => 'Oldest Post', 'published_at' => now()->subDays(9)]);
    $newest = publishedPost(['title' => 'Newest Post', 'author_name' => 'Ada Lovelace', 'published_at' => now()->subDay()]);
    publishedPost(['title' => 'Middle Post', 'published_at' => now()->subDays(3)]);
    publishedPost(['title' => 'Second Post', 'published_at' => now()->subDays(2)]);
    Post::factory()->withThemeImages()->create(['title' => 'Secret Draft']);

    $section = blogNewsHtml($this->get('/')->assertOk()->getContent());

    expect($section)
        ->toContain('Newest Post')
        ->toContain('Ada Lovelace')
        ->toContain($newest->published_at->format('F j'))
        ->toContain('href="'.route('blog.show', $newest->slug).'"')
        ->toContain('>Blog and news</span>')
        ->not->toContain('Oldest Post')
        ->not->toContain('Secret Draft')
        ->not->toContain('Mesbah')
        ->and(strpos($section, 'Newest Post'))->toBeLessThan(strpos($section, 'Second Post'))
        ->and(strpos($section, 'Second Post'))->toBeLessThan(strpos($section, 'Middle Post'));
});

test('the home section uses the saved heading and hides an emptied small heading', function () {
    publishedPost();
    Section::query()->create(['key' => Section::HOME_BLOG, 'eyebrow' => '', 'title' => "Line one\nLine two", 'is_active' => true]);

    $section = blogNewsHtml($this->get('/')->getContent());

    expect($section)
        ->toMatch('/Line one\s*<br>\s*Line two/')
        ->not->toContain('section-sub-title');
});

test('the home section is hidden when switched off or when nothing is published', function () {
    Post::factory()->withThemeImages()->create();
    $this->get('/')->assertOk()->assertDontSee('blog-and-news-are', false);

    publishedPost();
    $this->get('/')->assertSee('blog-and-news-are', false);

    Section::query()->create(['key' => Section::HOME_BLOG, 'title' => 'Blog', 'is_active' => false]);
    $this->get('/')->assertDontSee('blog-and-news-are', false);
});

test('changes show up on the home page right away', function () {
    $post = publishedPost(['title' => 'Before Title']);
    $this->get('/')->assertSee('Before Title');

    $post->update(['title' => 'After Title']);
    $this->get('/')->assertSee('After Title')->assertDontSee('Before Title');

    $post->update(['status' => Post::STATUS_DRAFT]);
    $this->get('/')->assertDontSee('After Title');
});

// ---------- Header ----------

test('the header links to the blog and projects pages', function () {
    $html = $this->get('/')->getContent();

    expect(substr_count($html, 'href="'.route('blog.index').'"'))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($html, 'href="'.route('projects.index').'"'))->toBeGreaterThanOrEqual(2)
        ->and($html)->not->toContain('href="blog-col-4.html"')
        ->not->toContain('href="blog-details.html"')
        ->not->toContain('href="project-style-two.html"');
});

// ---------- Blog list ----------

test('the blog lists published posts only, newest first', function () {
    $post = publishedPost(['title' => 'Live Post', 'author_name' => 'Grace Hopper', 'published_at' => now()->subDays(2)]);
    publishedPost(['title' => 'Newer Post', 'published_at' => now()->subDay()]);
    Post::factory()->create(['title' => 'Draft Post']);
    Post::factory()->withThemeImages()->create(['title' => 'Future Post', 'status' => 'published', 'published_at' => now()->addWeek()]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSeeInOrder(['Newer Post', 'Live Post'])
        ->assertSee('Grace Hopper')
        ->assertSee('href="'.route('blog.show', $post->slug).'"', false)
        ->assertDontSee('Draft Post')
        ->assertDontSee('Future Post');
});

test('the blog shows 9 posts per page and keeps the category in page links', function () {
    $category = PostCategory::factory()->create(['name' => 'Web Design']);
    $perPage = FrontendController::POSTS_PER_PAGE;

    foreach (range(1, $perPage + 2) as $i) {
        publishedPost(['title' => "Post Number {$i}", 'category_id' => $category->id, 'published_at' => now()->subDays($i)]);
    }

    $this->get(route('blog.index', ['category' => $category->slug]))
        ->assertSee('>Post Number 1<', false)
        ->assertDontSee('Post Number '.($perPage + 1).'<', false)
        ->assertSee('tmp-pagination-button', false)
        ->assertSee('category='.$category->slug.'&amp;page=2', false);

    $this->get(route('blog.index', ['category' => $category->slug, 'page' => 2]))
        ->assertSee('Post Number '.($perPage + 1))
        ->assertDontSee('>Post Number 1<', false);
});

test('category links only list categories with published posts and filter the list', function () {
    $design = PostCategory::factory()->create(['name' => 'Web Design']);
    $branding = PostCategory::factory()->create(['name' => 'Branding']);
    $empty = PostCategory::factory()->create(['name' => 'Empty Category']);

    publishedPost(['title' => 'Design Post', 'category_id' => $design->id]);
    publishedPost(['title' => 'Brand Post', 'category_id' => $branding->id]);
    Post::factory()->create(['title' => 'Hidden Draft', 'category_id' => $empty->id]);

    $this->get(route('blog.index'))
        ->assertSee('Web Design')->assertSee('Branding')
        ->assertDontSee('Empty Category');

    $html = $this->get(route('blog.index', ['category' => $design->slug]))
        ->assertOk()
        ->assertSee('Design Post')
        ->assertDontSee('Brand Post')
        ->getContent();

    expect($html)->toMatch('/class="active"\s+aria-current="page"\s*>Web Design<\/a>/');
});

test('an unknown or malformed category shows every post', function () {
    publishedPost(['title' => 'Any Post']);

    $this->get(route('blog.index', ['category' => 'does-not-exist']))->assertOk()->assertSee('Any Post');
    $this->get('/blog?category[]=x')->assertOk()->assertSee('Any Post');
});

test('the blog has a friendly message when nothing is published', function () {
    $this->get(route('blog.index'))->assertOk()->assertSee('No posts to show yet.');
});

// ---------- Blog details ----------

test('the details page shows everything about a published post', function () {
    $category = PostCategory::factory()->create(['name' => 'Web Design']);
    $post = publishedPost([
        'title' => 'Planning a Portfolio',
        'author_name' => 'Ada Lovelace',
        'category_id' => $category->id,
        'published_at' => '2024-03-05 10:00:00',
        'body' => '<h3>Start here</h3><p>Plan first.</p><blockquote>Less is more.</blockquote>',
        'featured_image_alt' => 'A tidy desk',
    ]);
    $post->tags()->sync([Tag::factory()->create(['name' => 'Figma'])->id => ['sort_order' => 0]]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('<h2 class="title blog-post-title">Planning a Portfolio</h2>', false)
        ->assertSee('By Ada Lovelace')
        ->assertSee('March 5, 2024')
        ->assertSee('href="'.route('blog.index', ['category' => $category->slug]).'"', false)
        ->assertSee('<h3>Start here</h3><p>Plan first.</p><blockquote>Less is more.</blockquote>', false)
        ->assertSee('alt="A tidy desk"', false)
        ->assertSee('<p class="tag">Figma</p>', false)
        ->assertSee('Comments (00)')
        ->assertSee('Leave a comment')
        ->assertDontSee('Stanio lainto');
});

test('drafts, future posts and unknown slugs are 404', function () {
    $draft = Post::factory()->withThemeImages()->create();
    $future = Post::factory()->withThemeImages()->create(['status' => 'published', 'published_at' => now()->addDay()]);

    $this->get(route('blog.show', $draft->slug))->assertNotFound();
    $this->get(route('blog.show', $future->slug))->assertNotFound();
    $this->get(route('blog.show', 'no-such-post'))->assertNotFound();
});

test('previous is the older post, next the newer one, and drafts are skipped', function () {
    $old = publishedPost(['title' => 'Old Post', 'published_at' => now()->subDays(3)]);
    Post::factory()->create(['title' => 'Draft Between', 'published_at' => now()->subDays(2)]);
    $new = publishedPost(['title' => 'New Post', 'published_at' => now()->subDay()]);

    $this->get(route('blog.show', $new->slug))
        ->assertSee('href="'.route('blog.show', $old->slug).'" class="prev-btn"', false)
        ->assertDontSee('class="next-btn"', false)
        ->assertDontSee('Draft Between');

    $this->get(route('blog.show', $old->slug))
        ->assertSee('href="'.route('blog.show', $new->slug).'" class="next-btn"', false)
        ->assertDontSee('class="prev-btn"', false);
});

test('the sidebar lists published categories with counts, recent posts, the banner author and tags', function () {
    BannerSection::query()->create(BannerSection::defaults());
    $design = PostCategory::factory()->create(['name' => 'Web Design']);
    $draftOnly = PostCategory::factory()->create(['name' => 'Draft Only']);

    $post = publishedPost(['title' => 'Current Post', 'category_id' => $design->id]);
    $post->tags()->sync([Tag::factory()->create(['name' => 'Figma'])->id => ['sort_order' => 0]]);
    publishedPost(['title' => 'Recent One', 'category_id' => $design->id]);
    $draft = Post::factory()->create(['title' => 'Draft Post', 'category_id' => $draftOnly->id]);
    $draft->tags()->sync([Tag::factory()->create(['name' => 'Secret Tag'])->id => ['sort_order' => 0]]);

    $html = $this->get(route('blog.show', $post->slug))->getContent();
    $sidebar = substr($html, strpos($html, '<aside class="tmp-sidebar"'));

    expect($sidebar)
        ->toContain('Web Design')
        ->toContain('(02)')
        ->toContain('Recent One')
        ->toContain('Brooklyn Simmons')
        ->toContain('Product Designer')
        ->toContain('<span class="tag-link">Figma</span>')
        ->not->toContain('Draft Only')
        ->not->toContain('Draft Post')
        ->not->toContain('Secret Tag')
        ->not->toContain('Current Post')
        ->not->toContain('search-area');
});

test('text is escaped and the body can not run scripts on the public pages', function () {
    $post = publishedPost([
        'title' => '<script>alert(1)</script>',
        'author_name' => '<img src=x onerror=alert(2)>',
        'featured_image_alt' => '"><b>alt</b>',
        'body' => '<p>Safe</p><script>alert(3)</script><a href="javascript:alert(4)">x</a><p onclick="alert(5)">y</p>',
    ]);
    $post->tags()->sync([Tag::factory()->create(['name' => '<i>tag</i>', 'slug' => 'i-tag'])->id => ['sort_order' => 0]]);

    foreach (['/', route('blog.index'), route('blog.show', $post->slug)] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertDontSee('"><b>alt</b>', false)
            ->assertDontSee('<script>alert(3)</script>', false)
            ->assertDontSee('javascript:alert(4)', false)
            ->assertDontSee('onclick="alert(5)"', false)
            ->assertDontSee('<i>tag</i>', false);
    }
});

test('the blog pages load the theme scripts once and the custom styles', function () {
    $post = publishedPost();

    foreach ([route('blog.index'), route('blog.show', $post->slug)] as $url) {
        $html = $this->get($url)->getContent();

        expect(substr_count($html, 'frontend/assets/js/main.js'))->toBe(1)
            ->and($html)->toContain('frontend/custom/custom.css');
    }
});

test('the admin list links published posts to the site', function () {
    $post = publishedPost();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.posts.index'))
        ->assertSee('href="'.route('blog.show', $post->slug).'"', false);
});

test('the seeded demo posts render on all three pages', function () {
    $this->seed(PostSeeder::class);
    $post = Post::query()->published()->newestFirst()->first();

    expect(blogNewsHtml($this->get('/')->assertOk()->getContent()))->toContain(e($post->title));
    $this->get(route('blog.index'))->assertOk()->assertSee($post->title)->assertSee('Web Design');
    $this->get(route('blog.show', $post->slug))->assertOk()->assertSee($post->title)->assertSee('Good design is obvious.');
});
