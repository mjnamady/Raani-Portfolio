<?php

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('guests and non-admins can not manage categories', function () {
    $category = ProjectCategory::factory()->create();
    $user = User::factory()->create();

    $this->get(route('admin.project-categories.index'))->assertRedirect(route('login'));
    $this->post(route('admin.project-categories.store'), ['name' => 'X'])->assertRedirect(route('login'));

    $this->actingAs($user)->get(route('admin.project-categories.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.project-categories.store'), ['name' => 'X'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.project-categories.update', $category), ['name' => 'X'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.project-categories.destroy', $category))->assertForbidden();

    expect(ProjectCategory::count())->toBe(1);
});

test('admin can add categories with generated, unique slugs', function () {
    $this->actingAs($this->admin)->post(route('admin.project-categories.store'), ['name' => 'Content writing'])->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('admin.project-categories.store'), ['name' => 'Content Writing'])->assertSessionHasNoErrors();

    expect(ProjectCategory::query()->ordered()->pluck('slug')->all())->toBe(['content-writing', 'content-writing-2']);
});

test('the categories page lists categories with their project counts', function () {
    $category = ProjectCategory::factory()->create(['name' => 'Branding']);
    Project::factory()->count(2)->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->get(route('admin.project-categories.index'))
        ->assertOk()
        ->assertSee('value="Branding"', false)
        ->assertSee('2 projects');
});

test('admin can rename a category; rename errors use their own error bag', function () {
    $category = ProjectCategory::factory()->create(['name' => 'Old']);
    ProjectCategory::factory()->create(['slug' => 'taken']);

    $this->actingAs($this->admin)
        ->put(route('admin.project-categories.update', $category), ['name' => 'New Name', 'slug' => ''])
        ->assertSessionHasNoErrors();

    expect($category->fresh()->only(['name', 'slug']))->toBe(['name' => 'New Name', 'slug' => 'old']);

    $this->actingAs($this->admin)
        ->put(route('admin.project-categories.update', $category), ['name' => 'New Name', 'slug' => 'taken'])
        ->assertSessionHasErrorsIn('updateCategory', 'slug');
});

test('categories are validated', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.project-categories.store'), $payload)
        ->assertSessionHasErrors($field);
})->with([
    'name missing' => [['name' => ''], 'name'],
    'name too long' => [['name' => str_repeat('a', 61)], 'name'],
]);

test('deleting a category keeps its projects, uncategorised', function () {
    $category = ProjectCategory::factory()->create();
    $project = Project::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)->delete(route('admin.project-categories.destroy', $category))->assertSessionHas('status');

    expect(ProjectCategory::count())->toBe(0)
        ->and($project->fresh())->not->toBeNull()
        ->and($project->fresh()->category_id)->toBeNull();
});

test('categories can be reordered', function () {
    $a = ProjectCategory::factory()->create(['name' => 'A']);
    $a->forceFill(['sort_order' => 1])->save();
    $b = ProjectCategory::factory()->create(['name' => 'B']);
    $b->forceFill(['sort_order' => 2])->save();

    $this->actingAs($this->admin)->patch(route('admin.project-categories.move', $b), ['direction' => 'up']);

    expect(ProjectCategory::query()->ordered()->pluck('name')->all())->toBe(['B', 'A']);
});

test('category names are escaped on the page', function () {
    ProjectCategory::factory()->create(['name' => '<script>alert(1)</script>']);

    $this->actingAs($this->admin)->get(route('admin.project-categories.index'))
        ->assertDontSee('<script>alert(1)</script>', false);
});
