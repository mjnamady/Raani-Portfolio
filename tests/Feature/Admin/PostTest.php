<?php

use App\Http\Controllers\Admin\PostController;
use App\Models\BannerSection;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Models\User;
use App\Services\ImageUploadService;
use Database\Seeders\PostSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');
    $this->withoutVite();

    $this->admin = User::factory()->admin()->create();
    $this->category = PostCategory::factory()->create(['name' => 'Web Design']);
});

function postPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'How I Plan a Portfolio',
        'slug' => '',
        'excerpt' => '  A short   summary.  ',
        'category_id' => test()->category->id,
        'body' => '<h3>Start here</h3><p>Plan <strong>first</strong>.</p><blockquote>Less is more.</blockquote><ul><li>One</li></ul>',
        'tags' => 'UX Design, Graphics, ux design',
        'author_name' => 'Ada Lovelace',
        'status' => 'draft',
        'published_at' => '',
        'featured_image_alt' => 'A desk with a laptop',
        'meta_title' => '',
        'meta_description' => '',
    ], $overrides);
}

function postImage(int $width = 1200, int $height = 700): UploadedFile
{
    return UploadedFile::fake()->image('cover.jpg', $width, $height);
}

// ---------- Access ----------

test('guests are redirected from every blog admin route', function () {
    $post = Post::factory()->create();

    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
    $this->get(route('admin.posts.create'))->assertRedirect(route('login'));
    $this->post(route('admin.posts.store'), postPayload())->assertRedirect(route('login'));
    $this->get(route('admin.posts.edit', $post))->assertRedirect(route('login'));
    $this->put(route('admin.posts.update', $post), postPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.posts.destroy', $post))->assertRedirect(route('login'));
    $this->patch(route('admin.posts.publish', $post))->assertRedirect(route('login'));
    $this->put(route('admin.posts.settings'), ['title' => 'X'])->assertRedirect(route('login'));

    expect(Post::query()->count())->toBe(1);
});

test('non-admin users can not view or change posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['title' => 'Untouched']);

    $this->actingAs($user)->get(route('admin.posts.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.posts.create'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.posts.edit', $post))->assertForbidden();
    $this->actingAs($user)->post(route('admin.posts.store'), postPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.posts.update', $post), postPayload())->assertForbidden();
    $this->actingAs($user)->patch(route('admin.posts.publish', $post))->assertForbidden();
    $this->actingAs($user)->put(route('admin.posts.settings'), ['title' => 'Hacked'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.posts.destroy', $post))->assertForbidden();

    expect($post->fresh())->title->toBe('Untouched')->status->toBe('draft')
        ->and(Post::query()->count())->toBe(1)
        ->and(Section::query()->where('key', Section::HOME_BLOG)->exists())->toBeFalse();
});

test('the policy only allows the admin', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $post))->toBeTrue()
        ->and($this->admin->can('delete', $post))->toBeTrue()
        ->and($user->can('update', $post))->toBeFalse()
        ->and($user->can('delete', $post))->toBeFalse()
        ->and($user->can('create', Post::class))->toBeFalse();
});

// ---------- Admin pages ----------

test('the posts page lists posts with status tabs, counts and add links', function () {
    Post::factory()->published()->withThemeImages()->create(['title' => 'Live Post', 'category_id' => $this->category->id]);
    Post::factory()->create(['title' => 'Draft Post']);

    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertOk()
        ->assertSee('Live Post')
        ->assertSee('Draft Post')
        ->assertSee('Web Design')
        ->assertSeeInOrder(['All', '2', 'Published', '1', 'Drafts', '1'])
        ->assertSee('href="'.route('admin.posts.create').'"', false)
        ->assertSee('Write a new post');

    $this->actingAs($this->admin)->get(route('admin.posts.index', ['status' => 'draft']))
        ->assertSee('Draft Post')
        ->assertDontSee('Live Post');

    $this->actingAs($this->admin)->get(route('admin.posts.index', ['status' => 'published']))
        ->assertSee('Live Post')
        ->assertDontSee('Draft Post');
});

test('an unknown status filter shows every post', function () {
    Post::factory()->create(['title' => 'Any Post']);

    $this->actingAs($this->admin)->get(route('admin.posts.index', ['status' => ['x']]))->assertOk()->assertSee('Any Post');
    $this->actingAs($this->admin)->get(route('admin.posts.index', ['status' => 'nope']))->assertOk()->assertSee('Any Post');
});

test('the posts page is paginated', function () {
    Post::factory()->count(PostController::PER_PAGE + 2)->create();

    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertOk()
        ->assertSee('page=2', false);
});

test('the empty page explains the home section is hidden', function () {
    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertOk()
        ->assertSee('Write your first post')
        ->assertSee('stays hidden on the site until a post is published');
});

test('the new post form is prefilled with the banner name as author', function () {
    BannerSection::query()->create(BannerSection::defaults());

    $this->actingAs($this->admin)->get(route('admin.posts.create'))
        ->assertOk()
        ->assertSee('value="Brooklyn Simmons"', false)
        ->assertSee('action="'.route('admin.posts.store').'"', false)
        ->assertSee('data-rich-text', false);
});

test('the edit form shows the post with its tags', function () {
    $post = Post::factory()->create(['title' => 'Editable']);
    $post->tags()->sync([Tag::factory()->create(['name' => 'Figma'])->id => ['sort_order' => 0]]);

    $this->actingAs($this->admin)->get(route('admin.posts.edit', $post))
        ->assertOk()
        ->assertSee('value="Editable"', false)
        ->assertSee('value="Figma"', false)
        ->assertSee('action="'.route('admin.posts.update', $post).'"', false);
});

test('the sidebar links to the posts and categories pages', function () {
    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertSee('href="'.route('admin.posts.index').'"', false)
        ->assertSee('href="'.route('admin.post-categories.index').'"', false);
});

// ---------- Create and update ----------

test('the admin can save a draft without an image', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload())
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'The post has been saved as a draft.');

    $post = Post::query()->firstOrFail();

    expect($post)
        ->slug->toBe('how-i-plan-a-portfolio')
        ->excerpt->toBe('A short summary.')
        ->status->toBe('draft')
        ->published_at->toBeNull()
        ->author_name->toBe('Ada Lovelace')
        ->category_id->toBe($this->category->id)
        ->featured_image->toBeNull()
        ->and($post->tags->pluck('name')->all())->toBe(['UX Design', 'Graphics']);
});

test('saving redirects to the edit page', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), postPayload());

    $response->assertRedirect(route('admin.posts.edit', Post::query()->firstOrFail()));
});

test('publishing stores the featured image in both sizes and sets the publish date', function () {
    $this->freezeTime();

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['status' => 'published', 'featured_image' => postImage()]))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'The post has been published.');

    $post = Post::query()->firstOrFail();

    expect($post->published_at->format('Y-m-d H:i:s'))->toBe(now()->format('Y-m-d H:i:s'))
        ->and($post->featured_image)->toStartWith('posts/')->toEndWith('.webp')
        ->and($post->card_image)->toStartWith('posts/')->not->toBe($post->featured_image);

    Storage::disk('public')->assertExists([$post->featured_image, $post->card_image]);

    $featured = getimagesizefromstring(Storage::disk('public')->get($post->featured_image));
    $card = getimagesizefromstring(Storage::disk('public')->get($post->card_image));

    expect([$featured[0], $featured[1]])->toBe([Post::FEATURED_WIDTH, Post::FEATURED_HEIGHT])
        ->and([$card[0], $card[1]])->toBe([Post::CARD_WIDTH, Post::CARD_HEIGHT]);
});

test('publishing needs the post text and a featured image', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['status' => 'published', 'body' => '']))
        ->assertSessionHasErrors(['body', 'featured_image']);

    expect(Post::query()->count())->toBe(0);
});

test('an existing image is enough to publish on update', function () {
    $post = Post::factory()->withThemeImages()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.posts.update', $post), postPayload(['status' => 'published']))
        ->assertSessionHasNoErrors();

    expect($post->fresh()->isPublished())->toBeTrue();
});

test('a back-dated publish date is kept, a future one is refused', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['status' => 'published', 'featured_image' => postImage(), 'published_at' => '2024-03-05T10:30']))
        ->assertSessionHasNoErrors();

    expect(Post::query()->first()->published_at->format('Y-m-d H:i'))->toBe('2024-03-05 10:30');

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['title' => 'Future', 'published_at' => now()->addDay()->format('Y-m-d\TH:i')]))
        ->assertSessionHasErrors('published_at');

    expect(Post::query()->where('title', 'Future')->exists())->toBeFalse();
});

test('replacing the image deletes the old uploads, theme images are never deleted', function () {
    $post = Post::factory()->withThemeImages()->create();

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['featured_image' => postImage()]));
    $post->refresh();
    $first = [$post->featured_image, $post->card_image];
    Storage::disk('public')->assertExists($first);
    expect(file_exists(public_path(Post::THEME_IMAGE_PREFIX.'blog/details/01.png')))->toBeTrue();

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['featured_image' => postImage()]));
    $post->refresh();

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists([$post->featured_image, $post->card_image]);
});

test('an image that can not be processed is reported without saving anything', function () {
    Exceptions::fake();
    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeCropped')->andThrow(new RuntimeException('broken'));
    });

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['featured_image' => postImage()]))
        ->assertSessionHasErrors(['featured_image' => 'The image could not be processed. Please try a different image.']);

    expect(Post::query()->count())->toBe(0);
});

test('images are checked for type, size and dimensions', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['featured_image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')]))
        ->assertSessionHasErrors('featured_image');

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['featured_image' => postImage(300, 200)]))
        ->assertSessionHasErrors('featured_image');

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['featured_image' => UploadedFile::fake()->image('huge.jpg', 1200, 700)->size(5000)]))
        ->assertSessionHasErrors('featured_image');

    expect(Post::query()->count())->toBe(0);
});

test('the slug is made unique and kept when left empty on edit', function () {
    Post::factory()->create(['title' => 'How I Plan a Portfolio']);

    $this->actingAs($this->admin)->post(route('admin.posts.store'), postPayload());
    $post = Post::query()->latest('id')->first();
    expect($post->slug)->toBe('how-i-plan-a-portfolio-2');

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['title' => 'A New Title', 'slug' => '']));
    expect($post->fresh()->slug)->toBe('how-i-plan-a-portfolio-2');

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['slug' => 'My Custom Slug!']));
    expect($post->fresh()->slug)->toBe('my-custom-slug');
});

test('a slug used by another post is refused', function () {
    Post::factory()->create(['slug' => 'taken']);

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['slug' => 'taken']))
        ->assertSessionHasErrors(['slug' => 'Another post already uses this URL slug.']);
});

test('tags are synced in order and removed when cleared', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['tags' => 'Branding, Figma']));
    expect($post->fresh()->tags->pluck('name')->all())->toBe(['Branding', 'Figma']);

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['tags' => '']));
    expect($post->fresh()->tags)->toBeEmpty();
});

test('posts and projects share the tags table', function () {
    $tag = Tag::factory()->create(['name' => 'Figma', 'slug' => 'figma']);

    $this->actingAs($this->admin)->post(route('admin.posts.store'), postPayload(['tags' => 'figma']));

    expect(Tag::query()->count())->toBe(1)
        ->and($tag->posts()->count())->toBe(1);
});

test('the body is sanitized: scripts, event handlers and javascript links are removed', function () {
    $this->actingAs($this->admin)->post(route('admin.posts.store'), postPayload([
        'body' => '<p onclick="alert(1)">Hi</p><script>alert(2)</script><a href="javascript:alert(3)">x</a><img src=x onerror=alert(4)><blockquote>Quote</blockquote>',
    ]));

    $body = Post::query()->first()->body;

    expect($body)
        ->toContain('<p>Hi</p>')
        ->toContain('<blockquote>Quote</blockquote>')
        ->not->toContain('onclick')
        ->not->toContain('<script')
        ->not->toContain('javascript:')
        ->not->toContain('<img');
});

// ---------- Validation ----------

test('required fields and lengths are validated', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload(['title' => '', 'author_name' => '', 'status' => 'scheduled']))
        ->assertSessionHasErrors(['title', 'author_name', 'status']);

    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'title' => str_repeat('a', 161),
            'excerpt' => str_repeat('b', 301),
            'author_name' => str_repeat('c', 81),
            'featured_image_alt' => str_repeat('d', 161),
            'meta_title' => str_repeat('e', 71),
            'meta_description' => str_repeat('f', 161),
        ]))
        ->assertSessionHasErrors(['title', 'excerpt', 'author_name', 'featured_image_alt', 'meta_title', 'meta_description']);

    expect(Post::query()->count())->toBe(0);
});

test('an unknown category and too many tags are refused', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'category_id' => 999,
            'tags' => implode(',', range(1, 11)),
        ]))
        ->assertSessionHasErrors(['category_id', 'tags']);
});

test('array input is rejected without an error page', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.posts.store'), postPayload([
            'title' => ['x'], 'slug' => ['y'], 'excerpt' => ['z'], 'body' => ['b'],
            'author_name' => ['a'], 'tags' => [['nested']], 'meta_title' => ['m'],
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors(['title', 'slug', 'excerpt', 'body', 'author_name', 'tags.0', 'meta_title']);

    expect(Post::query()->count())->toBe(0);
});

// ---------- Publish toggle and delete ----------

test('the admin can publish and unpublish from the list', function () {
    $post = Post::factory()->withThemeImages()->create();

    $this->actingAs($this->admin)->patch(route('admin.posts.publish', $post))
        ->assertSessionHas('status', 'The post is now published.');
    expect($post->fresh())->isPublished()->toBeTrue()->published_at->not->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.posts.publish', $post))
        ->assertSessionHas('status', 'The post is now a draft and hidden from the site.');
    expect($post->fresh()->isPublished())->toBeFalse();
});

test('a post without text or image can not be published from the list', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.posts.publish', $post))
        ->assertSessionHas('error', 'Add the post text and a featured image before publishing it.');

    expect($post->fresh()->isPublished())->toBeFalse();
});

test('deleting a post removes its uploads and tag links but keeps the tags', function () {
    $this->actingAs($this->admin)->post(route('admin.posts.store'), postPayload(['featured_image' => postImage()]));
    $post = Post::query()->firstOrFail();
    $files = [$post->featured_image, $post->card_image];

    $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post))
        ->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('status', 'The post has been deleted.');

    Storage::disk('public')->assertMissing($files);
    expect(Post::query()->count())->toBe(0)
        ->and(DB::table('post_tag')->count())->toBe(0)
        ->and(Tag::query()->count())->toBe(2);
});

test('the delete button asks for confirmation', function () {
    Post::factory()->create(['title' => 'Confirm Me']);

    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertSee('data-confirm="Delete the post &quot;Confirm Me&quot;? This cannot be undone."', false);
});

test('unknown posts are 404', function () {
    $this->actingAs($this->admin)->get(route('admin.posts.edit', 999))->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.posts.destroy', 999))->assertNotFound();
});

// ---------- Section settings, caching, escaping ----------

test('the admin can edit the home section heading and switch it off', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.posts.settings'), ['eyebrow' => 'Latest writing', 'title' => "Line one\nLine two", 'is_active' => '0'])
        ->assertRedirect(route('admin.posts.index'))
        ->assertSessionHas('status', 'The Blog and News section has been updated.');

    expect(Section::query()->where('key', Section::HOME_BLOG)->first())
        ->eyebrow->toBe('Latest writing')
        ->title->toBe("Line one\nLine two")
        ->is_active->toBeFalse();

    $this->actingAs($this->admin)->put(route('admin.posts.settings'), ['title' => ''])->assertSessionHasErrors('title');
    $this->actingAs($this->admin)->put(route('admin.posts.settings'), ['title' => ['x']])->assertSessionHasErrors('title');
});

test('saving, publishing, tagging and deleting clear the home cache', function () {
    $post = Post::factory()->published()->withThemeImages()->create(['title' => 'Before']);
    expect(Post::forHomePage()->pluck('title')->all())->toBe(['Before']);

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), postPayload(['title' => 'After', 'status' => 'published']));
    expect(Post::forHomePage()->pluck('title')->all())->toBe(['After']);

    $this->actingAs($this->admin)->patch(route('admin.posts.publish', $post));
    expect(Post::forHomePage())->toBeEmpty();

    $this->actingAs($this->admin)->patch(route('admin.posts.publish', $post));
    expect(Post::forHomePage())->toHaveCount(1);

    $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $post));
    expect(Post::forHomePage())->toBeEmpty();
});

test('the home cache only holds the latest published posts', function () {
    Post::factory()->published()->create(['title' => 'Old', 'published_at' => now()->subDays(10)]);
    Post::factory()->published()->create(['title' => 'Newest', 'published_at' => now()->subDay()]);
    Post::factory()->published()->create(['title' => 'Middle', 'published_at' => now()->subDays(5)]);
    Post::factory()->published()->create(['title' => 'Oldest', 'published_at' => now()->subDays(20)]);
    Post::factory()->create(['title' => 'Draft']);

    expect(Post::forHomePage()->pluck('title')->all())->toBe(['Newest', 'Middle', 'Old']);
});

test('text is escaped in the admin', function () {
    $post = Post::factory()->create([
        'title' => '<script>alert(1)</script>',
        'author_name' => '<img src=x onerror=alert(2)>',
    ]);
    $post->tags()->sync([Tag::factory()->create(['name' => '<b>tag</b>', 'slug' => 'b-tag'])->id => ['sort_order' => 0]]);

    foreach ([route('admin.posts.index'), route('admin.posts.edit', $post)] as $url) {
        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertDontSee('<b>tag</b>', false);
    }
});

// ---------- Seeder ----------

test('the seeder creates published demo posts once with categories, tags and a sanitized body', function () {
    // Start from an empty blog: beforeEach created a category, which the seeder would keep.
    $this->category->delete();

    $this->seed(PostSeeder::class);
    $this->seed(PostSeeder::class);

    expect(Post::query()->count())->toBe(6)
        ->and(Post::query()->published()->count())->toBe(6)
        ->and(PostCategory::query()->count())->toBe(3)
        ->and(Post::query()->whereNull('category_id')->count())->toBe(0)
        ->and(Post::query()->where('slug', '')->count())->toBe(0)
        ->and(Post::query()->first()->body)->toContain('<blockquote>')
        ->and(Section::query()->where('key', Section::HOME_BLOG)->value('eyebrow'))->toBe('Blog and news')
        ->and(Post::forHomePage())->toHaveCount(3);
});

test('the seeder never overwrites existing posts or categories', function () {
    Post::factory()->create(['title' => 'My Real Post']);

    $this->seed(PostSeeder::class);

    expect(Post::query()->pluck('title')->all())->toBe(['My Real Post'])
        ->and(PostCategory::query()->pluck('name')->all())->toBe(['Web Design']);
});
