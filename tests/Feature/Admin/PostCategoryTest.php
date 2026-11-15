<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function postCategoryNames(): array
{
    return PostCategory::query()->ordered()->pluck('name')->all();
}

test('guests and non-admins can not manage blog categories', function () {
    $category = PostCategory::factory()->create(['name' => 'Untouched']);
    $user = User::factory()->create();

    $this->get(route('admin.post-categories.index'))->assertRedirect(route('login'));
    $this->post(route('admin.post-categories.store'), ['name' => 'X'])->assertRedirect(route('login'));

    $this->actingAs($user)->get(route('admin.post-categories.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.post-categories.store'), ['name' => 'X'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.post-categories.update', $category), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($user)->patch(route('admin.post-categories.move', $category), ['direction' => 'up'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.post-categories.destroy', $category))->assertForbidden();

    expect(postCategoryNames())->toBe(['Untouched']);
});

test('the categories page lists categories with post counts', function () {
    $category = PostCategory::factory()->create(['name' => 'Web Design']);
    Post::factory()->count(2)->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->get(route('admin.post-categories.index'))
        ->assertOk()
        ->assertSee('value="Web Design"', false)
        ->assertSee('2 posts')
        ->assertSee('Its 2 post(s) will become uncategorised.');
});

test('the admin can add a category, which goes last with a unique slug', function () {
    PostCategory::factory()->create(['name' => 'Branding']);

    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => 'Branding'])
        ->assertRedirect(route('admin.post-categories.index'))
        ->assertSessionHas('status', 'The category "Branding" has been added.');

    $added = PostCategory::query()->latest('id')->first();

    expect($added->slug)->toBe('branding-2')
        ->and($added->sort_order)->toBe(1 + (int) PostCategory::query()->whereKeyNot($added->id)->max('sort_order'));
});

test('slugs made from long or non-Latin names fit their columns, suffix included', function () {
    // SQLite ignores column lengths, so the lengths are checked here; MySQL would refuse longer slugs.
    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => str_repeat('@', 60)])->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => str_repeat('@', 60)])->assertSessionHasNoErrors();

    expect(PostCategory::query()->pluck('slug')->map(fn (string $slug) => strlen($slug))->max())->toBeLessThanOrEqual(80)
        ->and(strlen(ProjectCategory::uniqueSlug(str_repeat('Щ', 60))))->toBeLessThanOrEqual(80)
        ->and(strlen(Service::uniqueSlug(str_repeat('Щ', 60))))->toBeLessThanOrEqual(100);
});

test('renaming keeps the slug when it is left empty', function () {
    $category = PostCategory::factory()->create(['name' => 'Web Design']);

    $this->actingAs($this->admin)->put(route('admin.post-categories.update', $category), ['name' => 'Web', 'slug' => '']);
    expect($category->fresh())->name->toBe('Web')->slug->toBe('web-design');

    $this->actingAs($this->admin)->put(route('admin.post-categories.update', $category), ['name' => 'Web', 'slug' => 'Web Stuff']);
    expect($category->fresh()->slug)->toBe('web-stuff');
});

test('category input is validated', function () {
    PostCategory::factory()->create(['slug' => 'taken']);

    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => ''])->assertSessionHasErrors('name');
    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => str_repeat('x', 61)])->assertSessionHasErrors('name');
    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => 'New', 'slug' => 'taken'])->assertSessionHasErrors('slug');
    $this->actingAs($this->admin)->post(route('admin.post-categories.store'), ['name' => ['x'], 'slug' => ['y']])->assertSessionHasErrors(['name', 'slug']);

    expect(PostCategory::query()->count())->toBe(1);
});

test('the admin can reorder categories', function () {
    PostCategory::factory()->create(['name' => 'A'])->forceFill(['sort_order' => 1])->save();
    $b = PostCategory::factory()->create(['name' => 'B']);
    $b->forceFill(['sort_order' => 2])->save();

    $this->actingAs($this->admin)->patch(route('admin.post-categories.move', $b), ['direction' => 'up']);
    expect(postCategoryNames())->toBe(['B', 'A']);

    $this->actingAs($this->admin)->patch(route('admin.post-categories.move', $b), ['direction' => 'sideways'])->assertSessionHasErrors('direction');
});

test('deleting a category leaves its posts uncategorised', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->delete(route('admin.post-categories.destroy', $category))
        ->assertSessionHas('status', 'The category has been deleted. Its posts are now uncategorised.');

    expect(PostCategory::query()->count())->toBe(0)
        ->and($post->fresh())->not->toBeNull()
        ->and($post->fresh()->category_id)->toBeNull();
});
