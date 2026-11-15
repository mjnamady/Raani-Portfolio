<?php

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\RichText;
use Database\Seeders\ProjectSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');
    $this->withoutVite();

    $this->admin = User::factory()->admin()->create();
    $this->category = ProjectCategory::factory()->create(['name' => 'Branding']);
});

function projectPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Fintech Mobile App',
        'slug' => '',
        'tagline' => 'Product design',
        'category_id' => test()->category->id,
        'client_name' => 'Acme Bank',
        'project_date' => '2024-06-01',
        'content' => '<h3>The brief</h3><p>Make banking <strong>simple</strong>.</p><ul><li>Research</li><li>Design</li></ul>',
        'features' => ['UI design', '', '  Prototyping  '],
        'tags' => 'Figma, Webflow, figma',
        'external_url' => 'https://acme.example',
        'is_featured' => '1',
        'status' => 'published',
        'published_at' => '',
    ], $overrides);
}

// ---------- Access ----------

test('guests are redirected from every project route', function () {
    $project = Project::factory()->create();

    $this->get(route('admin.projects.index'))->assertRedirect(route('login'));
    $this->get(route('admin.projects.create'))->assertRedirect(route('login'));
    $this->post(route('admin.projects.store'), projectPayload())->assertRedirect(route('login'));
    $this->get(route('admin.projects.edit', $project))->assertRedirect(route('login'));
    $this->put(route('admin.projects.update', $project), projectPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.projects.destroy', $project))->assertRedirect(route('login'));
    $this->patch(route('admin.projects.publish', $project))->assertRedirect(route('login'));
    $this->patch(route('admin.projects.feature', $project))->assertRedirect(route('login'));
    $this->patch(route('admin.projects.move', $project), ['direction' => 'up'])->assertRedirect(route('login'));
    $this->put(route('admin.projects.settings'), ['is_active' => '0'])->assertRedirect(route('login'));
});

test('non-admin users can not view or change projects', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['title' => 'Untouched']);

    $this->actingAs($user)->get(route('admin.projects.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.projects.store'), projectPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.projects.update', $project), projectPayload())->assertForbidden();
    $this->actingAs($user)->patch(route('admin.projects.publish', $project))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.projects.destroy', $project))->assertForbidden();

    expect($project->fresh()->title)->toBe('Untouched');
});

test('the policy only allows the admin', function () {
    $project = Project::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $project))->toBeTrue()
        ->and($user->can('update', $project))->toBeFalse()
        ->and($user->can('create', Project::class))->toBeFalse();
});

// ---------- Board ----------

test('the board lists projects with their status and can be filtered', function () {
    Project::factory()->published()->featured()->create(['title' => 'Live One', 'category_id' => $this->category->id]);
    Project::factory()->create(['title' => 'Draft One']);

    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertOk()
        ->assertSee('Live One')->assertSee('Draft One')
        ->assertSee('Published')->assertSee('Draft');

    $this->actingAs($this->admin)->get(route('admin.projects.index', ['status' => 'draft']))
        ->assertSee('Draft One')->assertDontSee('Live One');

    $this->actingAs($this->admin)->get(route('admin.projects.index', ['category' => $this->category->id]))
        ->assertSee('Live One')->assertDontSee('Draft One');

    $this->actingAs($this->admin)->get(route('admin.projects.index').'?category[]=1')
        ->assertOk()->assertSee('Live One')->assertSee('Draft One');
});

test('the board always offers a "New project" tile, with or without projects', function () {
    $createLink = 'href="'.route('admin.projects.create').'" class="pj-card pj-add-card"';

    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertOk()
        ->assertSee($createLink, false)
        ->assertSee('No projects yet.');

    Project::factory()->published()->create(['title' => 'Existing']);

    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertSee($createLink, false)
        ->assertSeeInOrder(['New project', 'Existing']);
});

test('published projects link to their public page from the board', function () {
    $live = Project::factory()->published()->create(['title' => 'Live One']);
    $draft = Project::factory()->create(['title' => 'Draft One']);

    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertSee('href="'.route('projects.show', $live->slug).'"', false)
        ->assertDontSee('href="'.route('projects.show', $draft->slug).'"', false)
        ->assertSee('href="'.route('projects.index').'"', false);
});

test('the sidebar Portfolio menu has Projects and Categories only', function () {
    $this->actingAs($this->admin)->get(route('admin.banner'))
        ->assertSee('href="'.route('admin.projects.index').'"', false)
        ->assertSee('href="'.route('admin.project-categories.index').'"', false)
        ->assertDontSee('Add Project');
});

test('adding a project from the board: open the form, save, and see it on the board', function () {
    $this->actingAs($this->admin)->get(route('admin.projects.create'))
        ->assertOk()
        ->assertSee('action="'.route('admin.projects.store').'"', false)
        ->assertSee('Create project');

    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload(['title' => 'Brand New Project', 'status' => 'draft']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.projects.edit', Project::query()->sole()))
        ->assertSessionHas('status', 'The project has been saved as a draft.');

    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertSee('Brand New Project')
        ->assertSee('Draft');
});

test('the sidebar opens the Portfolio menu on project pages', function () {
    $this->actingAs($this->admin)->get(route('admin.projects.index'))
        ->assertSee('href="'.route('admin.projects.index').'"', false)
        ->assertSee('href="'.route('admin.project-categories.index').'"', false)
        ->assertSee('id="portfolio-menu"', false);
});

test('the editor loads the rich-text editor bundle', function () {
    $this->actingAs($this->admin)->get(route('admin.projects.create'))
        ->assertOk()
        ->assertSee('data-rich-text', false)
        ->assertSee('id="pj-features"', false);
});

// ---------- Create ----------

test('admin can create a project with tags, features and sanitized content', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload())
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $project = Project::query()->sole();

    expect($project->only(['title', 'slug', 'tagline', 'client_name', 'status', 'is_featured', 'external_url']))->toBe([
        'title' => 'Fintech Mobile App',
        'slug' => 'fintech-mobile-app',
        'tagline' => 'Product design',
        'client_name' => 'Acme Bank',
        'status' => 'published',
        'is_featured' => true,
        'external_url' => 'https://acme.example',
    ])
        ->and($project->category_id)->toBe($this->category->id)
        ->and($project->project_date->format('Y-m-d'))->toBe('2024-06-01')
        ->and($project->published_at)->not->toBeNull()
        ->and($project->features)->toBe(['UI design', 'Prototyping'])
        ->and($project->tags->pluck('name')->all())->toBe(['Figma', 'Webflow'])
        ->and($project->content)->toContain('<h3>The brief</h3>')
        ->and($project->content)->toContain('<strong>simple</strong>');

    $this->assertModelExists($project);
});

test('a project saved as draft has no publish date', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload(['status' => 'draft']))->assertSessionHasNoErrors();

    expect(Project::query()->sole()->only(['status', 'published_at']))->toBe(['status' => 'draft', 'published_at' => null]);
});

test('slugs are generated, kept unique and can be set by hand', function () {
    Project::factory()->create(['title' => 'Taken', 'slug' => 'fintech-mobile-app']);

    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload())->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload(['slug' => 'My Custom Slug!']))->assertSessionHasNoErrors();

    expect(Project::query()->pluck('slug')->all())->toContain('fintech-mobile-app-2', 'my-custom-slug');
});

test('editing a project with an empty slug keeps its current URL', function () {
    $project = Project::factory()->create(['title' => 'Original Title']);

    $this->actingAs($this->admin)
        ->put(route('admin.projects.update', $project), projectPayload(['title' => 'Completely New Title', 'slug' => '']))
        ->assertSessionHasNoErrors();

    expect($project->fresh()->slug)->toBe('original-title');
});

test('a slug used by another project is rejected', function () {
    Project::factory()->create(['slug' => 'taken']);

    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload(['slug' => 'taken']))
        ->assertSessionHasErrors('slug');
});

test('tags are shared between projects and matched case-insensitively', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload(['tags' => 'Figma']));
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload(['title' => 'Second', 'tags' => 'FIGMA, Ai']));

    expect(Tag::query()->orderBy('slug')->pluck('slug')->all())->toBe(['ai', 'figma']);
});

// ---------- Rich text sanitizing ----------

test('scripts, event handlers and javascript links are stripped from content', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload([
        'content' => '<p onclick="alert(1)">Hi</p><script>alert(2)</script>'
            .'<a href="javascript:alert(3)">bad</a><a href="https://ok.example" target="_blank">good</a>'
            .'<img src=x onerror=alert(4)><iframe src="https://evil.example"></iframe><p style="color:red">styled</p>',
    ]))->assertSessionHasNoErrors();

    $content = Project::query()->sole()->content;

    expect($content)
        ->not->toContain('<script')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->not->toContain('<img')
        ->not->toContain('onerror')
        ->not->toContain('<iframe')
        ->not->toContain('style=')
        ->toContain('href="https://ok.example"')
        ->toContain('rel="noreferrer noopener"');
});

test('content that is only empty markup is stored as nothing', function () {
    expect(RichText::clean('<p><br></p>'))->toBeNull()
        ->and(RichText::clean('   '))->toBeNull()
        ->and(RichText::clean(null))->toBeNull();
});

test('content is sanitized whatever saves it', function () {
    $project = Project::factory()->create(['content' => '<p>Hello</p><script>alert(1)</script>']);

    expect($project->fresh()->content)->toBe('<p>Hello</p>');
});

// ---------- Validation ----------

test('project fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Project::count())->toBe(0);
})->with([
    'title missing' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 121)], 'title'],
    'tagline too long' => [['tagline' => str_repeat('a', 81)], 'tagline'],
    'unknown category' => [['category_id' => 99999], 'category_id'],
    'bad date' => [['project_date' => 'yesterday-ish'], 'project_date'],
    'invalid status' => [['status' => 'archived'], 'status'],
    'javascript link' => [['external_url' => 'javascript:alert(1)'], 'external_url'],
    'link without scheme' => [['external_url' => 'acme.example'], 'external_url'],
    'feature too long' => [['features' => [str_repeat('a', 81)]], 'features.0'],
    'too many features' => [['features' => array_fill(0, 13, 'Feature')], 'features'],
    'too many tags' => [['tags' => implode(',', range(1, 11))], 'tags'],
    'tag too long' => [['tags' => str_repeat('a', 41)], 'tags.0'],
]);

test('input that does not fit the database gets a validation error instead of a crash', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Project::count())->toBe(0);
})->with([
    'content that grows past the column when escaped' => [['content' => str_repeat('&', 30000)], 'content'],
    // 30 characters (within the 40-character limit) become a 90-character slug ("Щ" is "shh").
    'tag whose slug is longer than 60' => [['tags' => str_repeat('Щ', 30)], 'tags.0'],
    'publish date past 2038' => [['published_at' => '9999-12-31T00:00'], 'published_at'],
]);

test('array input where text is expected gets a validation error, not a crash', function (string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload([$field => ['x']]))
        ->assertSessionHasErrors();

    expect(Project::count())->toBe(0);
})->with(['slug', 'external_url']);

test('array tags are ignored instead of crashing', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload(['tags' => ['x']]))
        ->assertSessionHasNoErrors();

    expect(Project::query()->sole()->tags)->toHaveCount(0);
});

test('rich-text links may only open in a new tab', function () {
    $project = Project::factory()->create(['content' => '<p><a href="https://a.example" target="_top">a</a> <a href="https://b.example" target="_blank">b</a></p>']);

    expect($project->fresh()->content)
        ->not->toContain('_top')
        ->toContain('target="_blank"');
});

// ---------- Images ----------

test('card and cover images are cropped to their sizes and stored as WebP', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload([
        'card_image' => UploadedFile::fake()->image('card.jpg', 1600, 1600),
        'cover_image' => UploadedFile::fake()->image('cover.jpg', 2000, 1200),
    ]))->assertSessionHasNoErrors();

    $project = Project::query()->sole();

    foreach (['card_image' => [Project::CARD_WIDTH, Project::CARD_HEIGHT], 'cover_image' => [Project::COVER_WIDTH, Project::COVER_HEIGHT]] as $field => $size) {
        expect($project->{$field})->toMatch('#^projects/[0-9a-f-]{36}\.webp$#');
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($project->{$field}));
        expect([$width, $height])->toBe($size);
    }
});

test('replacing an image deletes the old file, and saving without one keeps it', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload([
        'card_image' => UploadedFile::fake()->image('first.jpg', 1000, 900),
    ]));
    $project = Project::query()->sole();
    $first = $project->card_image;

    $this->actingAs($this->admin)->put(route('admin.projects.update', $project), projectPayload(['title' => 'Renamed']));
    expect($project->fresh()->card_image)->toBe($first);

    $this->actingAs($this->admin)->put(route('admin.projects.update', $project), projectPayload([
        'card_image' => UploadedFile::fake()->image('second.jpg', 1000, 900),
    ]))->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($project->fresh()->card_image);
});

test('theme demo images are never deleted when replaced', function () {
    $this->seed(ProjectSeeder::class);
    $project = Project::query()->ordered()->first();
    $themeFile = public_path($project->card_image);

    $this->actingAs($this->admin)->put(route('admin.projects.update', $project), projectPayload([
        'card_image' => UploadedFile::fake()->image('new.jpg', 1000, 900),
    ]))->assertSessionHasNoErrors();

    expect(file_exists($themeFile))->toBeTrue();
});

test('invalid images are rejected and nothing is stored', function (string $field, UploadedFile $file) {
    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload([$field => $file]))
        ->assertSessionHasErrors($field);

    expect(Project::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'svg card' => ['card_image', fn () => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>')],
    'php cover' => ['cover_image', fn () => UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["c"]);')],
    'tiny card' => ['card_image', fn () => UploadedFile::fake()->image('tiny.jpg', 100, 100)],
    'short cover' => ['cover_image', fn () => UploadedFile::fake()->image('short.jpg', 1200, 200)],
]);

test('an image that cannot be processed gives a validation error and saves nothing', function () {
    Exceptions::fake();

    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeCropped')->andThrow(new RuntimeException('Decoder failed.'));
        $mock->shouldReceive('delete');
    });

    $this->actingAs($this->admin)
        ->post(route('admin.projects.store'), projectPayload(['card_image' => UploadedFile::fake()->image('x.jpg', 1000, 900)]))
        ->assertSessionHasErrors(['card_image' => 'The image could not be processed. Please try a different image.']);

    expect(Project::count())->toBe(0);
});

test('a failed save removes the new upload and keeps the old image', function () {
    Exceptions::fake();

    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload([
        'card_image' => UploadedFile::fake()->image('first.jpg', 1000, 900),
    ]));
    $project = Project::query()->sole();
    $old = $project->card_image;

    Project::saving(fn () => throw new RuntimeException('Database unavailable.'));

    $this->actingAs($this->admin)
        ->put(route('admin.projects.update', $project), projectPayload(['card_image' => UploadedFile::fake()->image('second.jpg', 1000, 900)]))
        ->assertServerError();

    expect($project->fresh()->card_image)->toBe($old)
        ->and(Storage::disk('public')->allFiles())->toBe([$old]);
});

// ---------- Board actions ----------

test('a project can be published, unpublished and featured from the board', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.projects.publish', $project))->assertSessionHas('status');
    expect($project->fresh()->isPublished())->toBeTrue()
        ->and($project->fresh()->published_at)->not->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.projects.publish', $project));
    expect($project->fresh()->isPublished())->toBeFalse();

    $this->actingAs($this->admin)->patch(route('admin.projects.feature', $project));
    expect($project->fresh()->is_featured)->toBeTrue();
});

test('projects can be moved up and down', function () {
    $first = Project::factory()->create(['title' => 'First']);
    $first->forceFill(['sort_order' => 1])->save();
    $second = Project::factory()->create(['title' => 'Second']);
    $second->forceFill(['sort_order' => 2])->save();

    $this->actingAs($this->admin)->patch(route('admin.projects.move', $second), ['direction' => 'up']);

    expect(Project::query()->ordered()->pluck('title')->all())->toBe(['Second', 'First']);

    $this->actingAs($this->admin)
        ->patch(route('admin.projects.move', $second), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

test('deleting a project removes its uploaded files and gallery', function () {
    $this->actingAs($this->admin)->post(route('admin.projects.store'), projectPayload([
        'card_image' => UploadedFile::fake()->image('card.jpg', 1000, 900),
    ]));
    $project = Project::query()->sole();
    $this->actingAs($this->admin)->post(route('admin.projects.images.store', $project), [
        'images' => [UploadedFile::fake()->image('g.jpg', 900, 700)],
    ]);

    expect(Storage::disk('public')->allFiles())->toHaveCount(2);

    $this->actingAs($this->admin)->delete(route('admin.projects.destroy', $project))->assertSessionHas('status');

    expect(Project::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('the home section can be switched off', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.projects.settings'), ['is_active' => '0'])
        ->assertSessionHasNoErrors();

    expect(Section::isVisible(Section::HOME_PORTFOLIO))->toBeFalse();
});

// ---------- Publishing rules and home cache ----------

test('the published scope hides drafts and future publish dates', function () {
    Project::factory()->published()->create(['title' => 'Live']);
    Project::factory()->create(['title' => 'Draft']);
    Project::factory()->create(['title' => 'Scheduled', 'status' => 'published', 'published_at' => now()->addWeek()]);

    expect(Project::query()->published()->pluck('title')->all())->toBe(['Live']);
});

test('the home list prefers featured published projects and falls back to the latest', function () {
    Project::factory()->published()->create(['title' => 'Plain', 'published_at' => now()->subDays(1)]);
    Project::factory()->published()->featured()->create(['title' => 'Featured']);
    Project::factory()->featured()->create(['title' => 'Featured Draft']);

    expect(Project::forHomePage()->pluck('title')->all())->toBe(['Featured']);

    Project::query()->update(['is_featured' => false]);
    Project::forgetCache();

    expect(Project::forHomePage()->pluck('title')->all())->toContain('Plain', 'Featured')
        ->not->toContain('Featured Draft');
});

// ---------- Seeder ----------

test('the seeder creates the demo projects once with categories, tags and gallery', function () {
    ProjectCategory::query()->delete();

    $this->seed(ProjectSeeder::class);
    $this->seed(ProjectSeeder::class);

    expect(Project::count())->toBe(4)
        ->and(Project::query()->published()->count())->toBe(4)
        ->and(ProjectCategory::query()->pluck('name')->all())->toContain('Design', 'Marketing')
        ->and(Project::query()->first()->images()->count())->toBe(3)
        ->and(Project::query()->first()->slug)->not->toBeEmpty();
});
