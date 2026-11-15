<?php

use App\Http\Requests\Admin\UpdateLatestServicesAreaRequest;
use App\Models\LatestService;
use App\Models\Section;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Database\Seeders\LatestServicesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->seed(LatestServicesSeeder::class);
});

function latestServicesPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'is_active' => '1',
        'eyebrow' => 'What I do',
        'title' => 'Building products people love',
        'subtitle' => 'From first sketch to launch, I help teams ship fast and well.',
        'image_alt' => 'Ada at her desk',
        'items' => [
            ['title' => 'Product Strategy', 'description' => 'Workshops that turn ideas into a clear roadmap.'],
            ['title' => 'Interface Design', 'description' => 'Accessible, consistent design systems.'],
            ['title' => 'Web Development', 'description' => 'Fast Laravel applications that scale.'],
        ],
    ], $overrides);
}

function latestSection(): Section
{
    return Section::query()->where('key', Section::HOME_LATEST_SERVICES)->sole();
}

// ---------- Access ----------

test('guests are redirected from the latest service area editor', function () {
    $this->get(route('admin.latest-services'))->assertRedirect(route('login'));
    $this->patch(route('admin.latest-services.update'), latestServicesPayload())->assertRedirect(route('login'));
});

test('non-admin users can not view or update the latest service area', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.latest-services'))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.latest-services.update'), latestServicesPayload())->assertForbidden();

    expect(latestSection()->title)->toBe('Inspiring The World One Project');
});

// ---------- Editor page ----------

test('the editor shows the current content from the database', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.latest-services'))
        ->assertOk()
        ->assertSee('value="Latest Service"', false)
        ->assertSee('value="Inspiring The World One Project"', false)
        ->assertSee('value="A Portfolio of Creativity"', false)
        ->assertSee('value="A Showcase of My Projects"', false)
        ->assertSee(asset(LatestService::DEFAULT_IMAGE));
});

test('the sidebar links to the latest service area editor', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertSee('href="'.route('admin.latest-services').'"', false)
        ->assertSee('Latest Service Area');
});

// ---------- Home page ----------

test('the home page shows the seeded latest service area', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('latest-service-area', false)
        ->assertSee('Inspiring The World One Project')
        ->assertSeeInOrder(['01.', 'A Portfolio of Creativity', '02.', 'My Portfolio of Innovation', '03.', 'A Showcase of My Projects'])
        ->assertSee(asset(LatestService::DEFAULT_IMAGE));
});

// ---------- Saving content ----------

test('admin can update the latest service area and the home page shows it', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.latest-services'))
        ->assertSessionHas('status');

    expect(latestSection()->only(['eyebrow', 'title', 'subtitle', 'image_alt']))->toBe([
        'eyebrow' => 'What I do',
        'title' => 'Building products people love',
        'subtitle' => 'From first sketch to launch, I help teams ship fast and well.',
        'image_alt' => 'Ada at her desk',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('What I do')
        ->assertSee('Building products people love')
        ->assertSee('From first sketch to launch, I help teams ship fast and well.')
        ->assertSee('alt="Ada at her desk"', false)
        ->assertSeeInOrder(['01.', 'Product Strategy', '02.', 'Interface Design', '03.', 'Web Development'])
        ->assertDontSee('A Portfolio of Creativity');
});

test('items are saved in order and replace the previous ones', function () {
    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload())->assertSessionHasNoErrors();

    $items = LatestService::query()->ordered()->get();

    expect($items->pluck('title')->all())->toBe(['Product Strategy', 'Interface Design', 'Web Development'])
        ->and($items->pluck('sort_order')->all())->toBe([1, 2, 3]);
});

test('saving clears the cached home page section', function () {
    $this->get('/')->assertSee('A Portfolio of Creativity');

    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload());

    $this->get('/')->assertSee('Product Strategy')->assertDontSee('A Portfolio of Creativity');
});

test('the cached section survives a serializing cache store with class unserialization disabled', function () {
    // The array store used in tests never serializes; the real database store does.
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('A Portfolio of Creativity');
    $this->get('/')->assertOk()->assertSee('A Portfolio of Creativity')->assertSee('Inspiring The World One Project');

    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload())->assertSessionHasNoErrors();
    auth()->logout();

    $this->get('/')->assertOk()->assertSee('Product Strategy');
    $this->get('/')->assertOk()->assertSee('Product Strategy')->assertSee('Building products people love');
});

test('the section can be hidden without losing its content', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload(['is_active' => '0']))
        ->assertSessionHasNoErrors();

    expect(latestSection()->is_active)->toBeFalse()
        ->and(LatestService::count())->toBe(3);

    $this->get('/')->assertOk()->assertDontSee('latest-service-area', false);
});

test('empty optional heading fields are hidden on the home page', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload(['eyebrow' => '', 'subtitle' => '']))
        ->assertSessionHasNoErrors();

    $html = $this->get('/')->assertOk()->getContent();

    // Other (still static) home sections use the same heading classes, so only look inside this one.
    preg_match('#<section class="latest-service-area.*?</section>#s', $html, $match);

    expect($match[0] ?? '')->toContain('Building products people love')
        ->not->toContain('section-sub-title')
        ->not->toContain('description section-sm');
});

test('the section is hidden when every item is removed', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'items' => [
                ['title' => '', 'description' => ''],
                ['title' => '', 'description' => ''],
                ['title' => '', 'description' => ''],
            ],
        ]))
        ->assertSessionHasNoErrors();

    expect(LatestService::count())->toBe(0);

    $this->get('/')->assertOk()->assertDontSee('latest-service-area', false);
});

test('latest service text is escaped on the home page', function () {
    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload([
        'title' => '<script>alert(1)</script>',
        'image_alt' => '" onerror="alert(1)',
        'items' => [0 => ['title' => '<img src=x onerror=alert(1)>']],
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('alt="" onerror="alert(1)"', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

// ---------- Validation ----------

test('heading fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(latestSection()->title)->toBe('Inspiring The World One Project');
})->with([
    'title missing' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 121)], 'title'],
    'eyebrow too long' => [['eyebrow' => str_repeat('a', 61)], 'eyebrow'],
    'description too long' => [['subtitle' => str_repeat('a', 256)], 'subtitle'],
    'alt text missing' => [['image_alt' => ''], 'image_alt'],
]);

test('empty item rows are skipped', function () {
    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload([
        'items' => [
            0 => ['title' => '', 'description' => ''],
            2 => ['title' => '', 'description' => ''],
        ],
    ]))->assertSessionHasNoErrors();

    expect(LatestService::query()->ordered()->pluck('title')->all())->toBe(['Interface Design']);

    $this->get('/')->assertSeeInOrder(['01.', 'Interface Design'])->assertDontSee('Product Strategy');
});

test('a half-filled item row reports the error on that row', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'items' => [1 => ['title' => 'Only a title', 'description' => '']],
        ]))
        ->assertSessionHasErrors('items.1.description')
        ->assertSessionDoesntHaveErrors('items.0.description');
});

test('items are validated', function (array $row, string $field) {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload(['items' => [0 => $row]]))
        ->assertSessionHasErrors($field);

    expect(LatestService::query()->ordered()->first()->title)->toBe('A Portfolio of Creativity');
})->with([
    'title too long' => [['title' => str_repeat('a', 121)], 'items.0.title'],
    'description too long' => [['description' => str_repeat('a', 301)], 'items.0.description'],
]);

test('no more than three items are accepted', function () {
    $payload = latestServicesPayload();
    $payload['items'][] = ['title' => 'Fourth', 'description' => 'One too many.'];

    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), $payload)
        ->assertSessionHasErrors('items');

    expect(LatestService::where('title', 'Fourth')->exists())->toBeFalse();
});

// ---------- Side image ----------

test('a new side image is resized, renamed and stored on the public disk', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'image' => UploadedFile::fake()->image('my photo.jpg', 1500, 1500),
        ]))
        ->assertSessionHasNoErrors();

    $path = latestSection()->image;

    expect($path)->toMatch('#^latest-services/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($path);

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect([$width, $height])->toBe([LatestService::IMAGE_WIDTH, LatestService::IMAGE_HEIGHT]);

    $this->get('/')->assertSee(Storage::disk('public')->url($path));
});

test('replacing the side image deletes the old file, and saving without one keeps it', function () {
    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload([
        'image' => UploadedFile::fake()->image('first.jpg', 800, 800),
    ]));
    $first = latestSection()->image;

    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload(['title' => 'Renamed']));
    expect(latestSection()->image)->toBe($first);

    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload([
        'image' => UploadedFile::fake()->image('second.jpg', 800, 800),
    ]));
    $second = latestSection()->image;

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

test('invalid side images are rejected', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload(['image' => $file]))
        ->assertSessionHasErrors('image');

    expect(latestSection()->image)->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'pdf' => fn () => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
    'php renamed to png' => fn () => UploadedFile::fake()->createWithContent('shell.png', '<?php system($_GET["c"]);'),
    'svg' => fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'),
    'gif' => fn () => UploadedFile::fake()->image('anim.gif', 600, 600),
    'larger than 4 MB' => fn () => UploadedFile::fake()->image('big.jpg', 800, 800)->size(5000),
    'too small' => fn () => UploadedFile::fake()->image('tiny.jpg', 200, 200),
    'too large in pixels' => fn () => UploadedFile::fake()->image('huge.jpg', ImageUploadService::MAX_SOURCE_DIMENSION + 1, 400),
]);

test('an image that cannot be processed gives a validation error and changes nothing', function () {
    Exceptions::fake();

    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeCropped')->andThrow(new RuntimeException('Decoder failed.'));
        $mock->shouldNotReceive('delete');
    });

    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'image' => UploadedFile::fake()->image('broken.jpg', 800, 800),
        ]))
        ->assertSessionHasErrors(['image' => 'The side image could not be processed. Please try a different image.']);

    expect(latestSection()->title)->toBe('Inspiring The World One Project');
    Exceptions::assertReported(RuntimeException::class);
});

test('a failed save keeps the old image and items and removes the new upload', function () {
    Exceptions::fake();

    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload([
        'image' => UploadedFile::fake()->image('first.jpg', 800, 800),
    ]));
    $oldPath = latestSection()->image;

    LatestService::creating(function () {
        throw new RuntimeException('Database unavailable.');
    });

    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'title' => 'Should not stick',
            'image' => UploadedFile::fake()->image('second.jpg', 800, 800),
        ]))
        ->assertServerError();

    expect(latestSection()->image)->toBe($oldPath)
        ->and(latestSection()->title)->toBe('Building products people love')
        ->and(LatestService::count())->toBe(3)
        ->and(Storage::disk('public')->allFiles())->toBe([$oldPath]);
});

test('the side image limit shown and enforced never exceeds what PHP accepts', function () {
    $limit = UploadLimit::kilobytes(UpdateLatestServicesAreaRequest::MAX_IMAGE_KILOBYTES);
    $label = UploadLimit::label(UpdateLatestServicesAreaRequest::MAX_IMAGE_KILOBYTES);

    $this->actingAs($this->admin)
        ->get(route('admin.latest-services'))
        ->assertSee('data-max-bytes="'.($limit * 1024).'"', false)
        ->assertSee("max {$label}");

    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'image' => UploadedFile::fake()->image('big.jpg', 800, 800)->size($limit + 1),
        ]))
        ->assertSessionHasErrors(['image' => "The side image may not be larger than {$label}."]);
});

test('a side image dropped by PHP before it reaches the app explains the size limit', function () {
    $path = tempnam(sys_get_temp_dir(), 'upl');

    $this->actingAs($this->admin)
        ->patch(route('admin.latest-services.update'), latestServicesPayload([
            'image' => new UploadedFile($path, 'too-big.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true),
        ]))
        ->assertSessionHasErrors(['image' => 'The side image could not be uploaded. Please choose a file smaller than '.UploadLimit::label(UpdateLatestServicesAreaRequest::MAX_IMAGE_KILOBYTES).'.']);

    expect(latestSection()->title)->toBe('Inspiring The World One Project');

    @unlink($path);
});

// ---------- Seeder ----------

test('the seeder recreates the theme content once and never overwrites edits', function () {
    $this->actingAs($this->admin)->patch(route('admin.latest-services.update'), latestServicesPayload());

    $this->seed(LatestServicesSeeder::class);

    expect(Section::where('key', Section::HOME_LATEST_SERVICES)->count())->toBe(1)
        ->and(latestSection()->title)->toBe('Building products people love')
        ->and(LatestService::query()->ordered()->pluck('title')->all())->toBe(['Product Strategy', 'Interface Design', 'Web Development']);
});
