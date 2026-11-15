<?php

use App\Http\Requests\Admin\StoreProjectImagesRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->withoutVite();

    $this->admin = User::factory()->admin()->create();
    $this->project = Project::factory()->create(['title' => 'Gallery Project']);
});

function galleryImages(int $count): array
{
    return array_map(fn ($i) => UploadedFile::fake()->image("g{$i}.jpg", 1200, 900), range(1, $count));
}

test('guests and non-admins can not change a gallery', function () {
    $user = User::factory()->create();

    $this->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)])->assertRedirect(route('login'));
    $this->actingAs($user)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)])->assertForbidden();

    expect(ProjectImage::count())->toBe(0);
});

test('several images can be added at once; they are cropped and stored as WebP', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(3)])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.projects.edit', $this->project).'#gallery');

    $images = $this->project->images()->get();

    expect($images)->toHaveCount(3)
        ->and($images->pluck('sort_order')->all())->toBe([1, 2, 3])
        ->and($images->first()->alt)->toBe('Gallery Project');

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($images->first()->path));
    expect([$width, $height])->toBe([ProjectImage::WIDTH, ProjectImage::HEIGHT])
        ->and($images->first()->path)->toMatch('#^projects/gallery/[0-9a-f-]{36}\.webp$#');
});

test('the edit page shows the gallery with alt text fields', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)]);

    $this->actingAs($this->admin)->get(route('admin.projects.edit', $this->project))
        ->assertOk()
        ->assertSee('id="gallery"', false)
        ->assertSee('value="Gallery Project"', false);
});

test('an image description can be changed', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)]);
    $image = $this->project->images()->first();

    $this->actingAs($this->admin)
        ->put(route('admin.projects.images.update', [$this->project, $image]), ['alt' => 'Login screen'])
        ->assertSessionHasNoErrors();

    expect($image->fresh()->alt)->toBe('Login screen');
});

test('images can be reordered', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(2)]);
    [$first, $second] = $this->project->images()->get()->all();

    $this->actingAs($this->admin)->patch(route('admin.projects.images.move', [$this->project, $second]), ['direction' => 'left']);

    expect($this->project->images()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('removing an image deletes its file', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)]);
    $image = $this->project->images()->first();

    $this->actingAs($this->admin)->delete(route('admin.projects.images.destroy', [$this->project, $image]))->assertSessionHas('status');

    expect(ProjectImage::count())->toBe(0);
    Storage::disk('public')->assertMissing($image->path);
});

test('an image can only be changed through its own project', function () {
    $other = Project::factory()->create();
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)]);
    $image = $this->project->images()->first();

    $this->actingAs($this->admin)->put(route('admin.projects.images.update', [$other, $image]), ['alt' => 'Hijack'])->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.projects.images.destroy', [$other, $image]))->assertNotFound();

    expect($image->fresh()->alt)->toBe('Gallery Project');
});

test('gallery uploads are validated', function (array $images, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.images.store', $this->project), ['images' => $images])
        ->assertSessionHasErrors($field);

    expect(ProjectImage::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'nothing chosen' => [[], 'images'],
    'too many at once' => [fn () => galleryImages(StoreProjectImagesRequest::MAX_PER_UPLOAD + 1), 'images'],
    'svg' => [fn () => [UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')], 'images.0'],
    'too small' => [fn () => [UploadedFile::fake()->image('tiny.jpg', 100, 100)], 'images.0'],
]);

test('a full gallery accepts no more images', function () {
    foreach (range(1, StoreProjectImagesRequest::MAX_PER_PROJECT) as $i) {
        $image = new ProjectImage(['alt' => 'x']);
        $image->path = "projects/gallery/{$i}.webp";
        $image->sort_order = $i;
        $this->project->images()->save($image);
    }

    $this->actingAs($this->admin)
        ->post(route('admin.projects.images.store', $this->project), ['images' => galleryImages(1)])
        ->assertSessionHasErrors('images');
});
