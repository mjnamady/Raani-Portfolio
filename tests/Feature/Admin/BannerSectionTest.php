<?php

use App\Http\Requests\Admin\UpdateBannerSectionRequest;
use App\Models\BannerSection;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Database\Seeders\BannerSectionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->seed(BannerSectionSeeder::class);
});

function bannerPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'greeting' => 'Hello, I am',
        'name' => 'Ada Lovelace',
        'role' => 'a Software Engineer',
        'biography_heading' => 'About me :',
        'biography' => 'I build fast, accessible web apps.',
        'contact_heading' => 'Reach me :',
        'image_alt' => 'Portrait of Ada Lovelace',
        'is_active' => '1',
        'stats' => [
            ['value' => '12', 'suffix' => '+', 'label' => 'Years coding'],
            ['value' => '85', 'suffix' => '', 'label' => 'Projects shipped'],
            ['value' => '3', 'suffix' => 'k', 'label' => 'Happy users'],
        ],
    ], $overrides);
}

function transparentPng(int $width, int $height): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledellipse($image, intdiv($width, 2), intdiv($height, 2), intdiv($width, 2), intdiv($height, 2), imagecolorallocate($image, 200, 30, 60));

    ob_start();
    imagepng($image);

    return UploadedFile::fake()->createWithContent('cutout.png', (string) ob_get_clean());
}

// ---------- Access ----------

test('guests are redirected from the banner editor', function () {
    $this->get(route('admin.banner'))->assertRedirect(route('login'));
    $this->patch(route('admin.banner.update'), bannerPayload())->assertRedirect(route('login'));
});

test('non-admin users can not view or update the banner', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.banner'))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.banner.update'), bannerPayload())->assertForbidden();

    expect(BannerSection::first()->name)->toBe('Brooklyn Simmons');
});

// ---------- Editor page ----------

test('the editor shows the current banner content from the database', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertOk()
        ->assertSee('value="Brooklyn Simmons"', false)
        ->assertSee('value="a Product Designer"', false)
        ->assertSee('value="Winning award"', false)
        ->assertSee('value="800"', false)
        ->assertSee(asset(BannerSection::DEFAULT_IMAGE));
});

test('the editor creates the banner row when none exists yet', function () {
    BannerSection::query()->delete();

    $this->actingAs($this->admin)->get(route('admin.banner'))->assertOk();

    expect(BannerSection::count())->toBe(1);
});

// ---------- Saving content ----------

test('admin can update every banner field and the home page shows it', function () {
    Setting::put('contact.address_lines', ['12 Analytical Street', 'London']);
    Setting::put('contact.emails', ['ada@example.test']);
    Setting::put('contact.phones', ['+44 (0) 20 7946 0000']);

    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.banner'))
        ->assertSessionHas('status');

    $banner = BannerSection::first();
    expect($banner->only(['greeting', 'name', 'role', 'biography', 'contact_heading', 'image_alt']))->toBe([
        'greeting' => 'Hello, I am',
        'name' => 'Ada Lovelace',
        'role' => 'a Software Engineer',
        'biography' => 'I build fast, accessible web apps.',
        'contact_heading' => 'Reach me :',
        'image_alt' => 'Portrait of Ada Lovelace',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Hello, I am Ada Lovelace', 'a Software Engineer'])
        ->assertSee('About me :')
        ->assertSee('I build fast, accessible web apps.')
        ->assertSee('Reach me :')
        ->assertSee('12 Analytical Street, London')
        ->assertSee('<a href="mailto:ada@example.test">ada@example.test</a>', false)
        ->assertSee('<a href="tel:+4402079460000">', false)
        ->assertSee('data-count="12"', false)
        ->assertSeeInOrder(['Years coding', 'Projects shipped', 'Happy users'])
        ->assertDontSee('Brooklyn Simmons')
        ->assertDontSee('Winning award');
});

test('saving clears the cached home page banner', function () {
    $this->get('/')->assertSee('Brooklyn Simmons');

    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload());

    $this->get('/')->assertSee('Ada Lovelace')->assertDontSee('Brooklyn Simmons');
});

test('the cached banner survives a serializing cache store with class unserialization disabled', function () {
    // The array store used in tests never serializes; the real database store does.
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('Brooklyn Simmons');

    // Second request reads the serialized entry back from the cache table.
    $this->get('/')->assertOk()->assertSee('Brooklyn Simmons')->assertSee('Winning award');

    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload())->assertSessionHasNoErrors();
    auth()->logout();

    $this->get('/')->assertOk()->assertSee('Ada Lovelace')->assertSee('Years coding');
    $this->get('/')->assertOk()->assertSee('Ada Lovelace');
});

test('the banner can be hidden without losing its content', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload(['is_active' => '0']))
        ->assertSessionHasNoErrors();

    expect(BannerSection::first()->is_active)->toBeFalse()
        ->and(BannerSection::first()->name)->toBe('Ada Lovelace');

    $this->get('/')->assertOk()->assertDontSee('rpp-banner-five-area');
});

test('the contact block is hidden while no contact details are set', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload())->assertSessionHasNoErrors();

    $this->get('/')->assertOk()->assertDontSee('Reach me :');

    Setting::put('contact.emails', ['ada@example.test']);

    $this->get('/')->assertSee('Reach me :')->assertSee('ada@example.test');
});

test('the editor shows the contact details read-only, with a link to where they are edited', function () {
    Setting::put('contact.emails', ['ada@example.test']);

    $this->actingAs($this->admin)->get(route('admin.banner'))
        ->assertOk()
        ->assertSee('ada@example.test')
        ->assertSee('No phone set')
        ->assertDontSee('name="email"', false)
        ->assertSee('href="'.route('admin.contact.index').'"', false);
});

test('the banner no longer stores its own contact details', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload(['email' => 'sneaky@example.test', 'address' => 'Somewhere']))
        ->assertSessionHasNoErrors();

    expect(Schema::hasColumns('banner_sections', ['address', 'email', 'phone']))->toBeFalse()
        ->and(Setting::get('contact.emails'))->toBeNull();
});

test('banner text is escaped on the home page', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload([
        'name' => '<script>alert(1)</script>',
        'biography' => '<img src=x onerror=alert(1)>',
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('banner fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(BannerSection::first()->name)->toBe('Brooklyn Simmons');
})->with([
    'greeting missing' => [['greeting' => ''], 'greeting'],
    'name missing' => [['name' => ''], 'name'],
    'name too long' => [['name' => str_repeat('a', 81)], 'name'],
    'role missing' => [['role' => ''], 'role'],
    'biography missing' => [['biography' => ''], 'biography'],
    'biography too long' => [['biography' => str_repeat('a', 601)], 'biography'],
    'biography heading missing' => [['biography_heading' => ''], 'biography_heading'],
    'contact heading missing' => [['contact_heading' => ''], 'contact_heading'],
    'alt text missing' => [['image_alt' => ''], 'image_alt'],
]);

// ---------- Counters ----------

test('counters are saved in order and replace the previous ones', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload())->assertSessionHasNoErrors();

    $stats = Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->get();

    expect($stats)->toHaveCount(3)
        ->and($stats->pluck('label')->all())->toBe(['Years coding', 'Projects shipped', 'Happy users'])
        ->and($stats->pluck('value')->all())->toBe([12, 85, 3])
        ->and($stats->pluck('sort_order')->all())->toBe([1, 2, 3])
        ->and(Stat::where('label', 'Winning award')->exists())->toBeFalse();
});

test('empty counter rows are skipped', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload([
        'stats' => [
            1 => ['value' => '', 'suffix' => '', 'label' => ''],
            2 => ['value' => '', 'suffix' => '', 'label' => ''],
        ],
    ]))->assertSessionHasNoErrors();

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->pluck('label')->all())->toBe(['Years coding']);

    $this->get('/')->assertSee('Years coding')->assertDontSee('Projects shipped');
});

test('a half-filled counter row reports the error on that row', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload([
            'stats' => [1 => ['value' => '50', 'suffix' => '', 'label' => '']],
        ]))
        ->assertSessionHasErrors('stats.1.label')
        ->assertSessionDoesntHaveErrors('stats.0.label');
});

test('counters are validated', function (array $row, string $field) {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload(['stats' => [0 => $row]]))
        ->assertSessionHasErrors("stats.0.{$field}");
})->with([
    'not a number' => [['value' => 'lots', 'suffix' => '', 'label' => 'Things'], 'value'],
    'decimal' => [['value' => '1.5', 'suffix' => '', 'label' => 'Things'], 'value'],
    'negative' => [['value' => '-1', 'suffix' => '', 'label' => 'Things'], 'value'],
    'too large' => [['value' => '10000000', 'suffix' => '', 'label' => 'Things'], 'value'],
    'suffix too long' => [['value' => '5', 'suffix' => 'abcdef', 'label' => 'Things'], 'suffix'],
    'label too long' => [['value' => '5', 'suffix' => '', 'label' => str_repeat('a', 61)], 'label'],
]);

test('no more than three counters are accepted', function () {
    $payload = bannerPayload();
    $payload['stats'][] = ['value' => '9', 'suffix' => '', 'label' => 'Fourth'];

    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), $payload)
        ->assertSessionHasErrors('stats');

    expect(Stat::where('label', 'Fourth')->exists())->toBeFalse();
});

// ---------- Portrait ----------

test('a new portrait is resized with Intervention Image, renamed and stored on the public disk', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload([
            'image' => UploadedFile::fake()->image('my portrait.jpg', 1500, 1500),
        ]))
        ->assertSessionHasNoErrors();

    $path = BannerSection::first()->image;

    expect($path)->toMatch('#^banner/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($path);

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect([$width, $height])->toBe([BannerSection::IMAGE_WIDTH, BannerSection::IMAGE_HEIGHT]);

    $this->get('/')->assertSee(Storage::disk('public')->url($path));
});

test('a transparent PNG portrait keeps its transparency', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload(['image' => transparentPng(900, 1100)]))
        ->assertSessionHasNoErrors();

    $stored = imagecreatefromstring(Storage::disk('public')->get(BannerSection::first()->image));
    $corner = imagecolorsforindex($stored, imagecolorat($stored, 0, 0));

    expect($corner['alpha'])->toBeGreaterThan(100);
});

test('replacing the portrait deletes the old file, and saving without one keeps it', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload([
        'image' => UploadedFile::fake()->image('first.jpg', 800, 1000),
    ]));
    $first = BannerSection::first()->image;

    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload(['name' => 'Renamed']));
    expect(BannerSection::first()->image)->toBe($first);

    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload([
        'image' => UploadedFile::fake()->image('second.jpg', 800, 1000),
    ]));
    $second = BannerSection::first()->image;

    expect($second)->not->toBe($first);
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);
});

test('invalid portraits are rejected', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload(['image' => $file]))
        ->assertSessionHasErrors('image');

    expect(BannerSection::first()->image)->toBeNull()
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
        ->patch(route('admin.banner.update'), bannerPayload([
            'image' => UploadedFile::fake()->image('broken.jpg', 800, 1000),
        ]))
        ->assertSessionHasErrors(['image' => 'The banner image could not be processed. Please try a different image.']);

    expect(BannerSection::first()->name)->toBe('Brooklyn Simmons');
    Exceptions::assertReported(RuntimeException::class);
});

test('a failed save keeps the old portrait and counters and removes the new upload', function () {
    Exceptions::fake();

    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload([
        'image' => UploadedFile::fake()->image('first.jpg', 800, 1000),
    ]));
    $oldPath = BannerSection::first()->image;

    Stat::creating(function () {
        throw new RuntimeException('Database unavailable.');
    });

    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload([
            'name' => 'Should not stick',
            'image' => UploadedFile::fake()->image('second.jpg', 800, 1000),
        ]))
        ->assertServerError();

    expect(BannerSection::first()->image)->toBe($oldPath)
        ->and(BannerSection::first()->name)->toBe('Ada Lovelace')
        ->and(Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->count())->toBe(3)
        ->and(Storage::disk('public')->allFiles())->toBe([$oldPath]);
});

test('the portrait limit shown and enforced never exceeds what PHP accepts', function () {
    $limit = UploadLimit::kilobytes(UpdateBannerSectionRequest::MAX_IMAGE_KILOBYTES);
    $label = UploadLimit::label(UpdateBannerSectionRequest::MAX_IMAGE_KILOBYTES);

    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertSee('data-max-bytes="'.($limit * 1024).'"', false)
        ->assertSee("up to {$label}");

    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload([
            'image' => UploadedFile::fake()->image('big.jpg', 800, 1000)->size($limit + 1),
        ]))
        ->assertSessionHasErrors(['image' => "The banner image may not be larger than {$label}."]);
});

test('a portrait dropped by PHP before it reaches the app explains the size limit', function () {
    $path = tempnam(sys_get_temp_dir(), 'upl');

    $this->actingAs($this->admin)
        ->patch(route('admin.banner.update'), bannerPayload([
            'image' => new UploadedFile($path, 'too-big.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true),
        ]))
        ->assertSessionHasErrors(['image' => 'The banner image could not be uploaded. Please choose a file smaller than '.UploadLimit::label(UpdateBannerSectionRequest::MAX_IMAGE_KILOBYTES).'.']);

    expect(BannerSection::first()->name)->toBe('Brooklyn Simmons');

    @unlink($path);
});

// ---------- Seeder ----------

test('the seeder recreates the theme content once and never overwrites edits', function () {
    $this->actingAs($this->admin)->patch(route('admin.banner.update'), bannerPayload());

    $this->seed(BannerSectionSeeder::class);

    expect(BannerSection::count())->toBe(1)
        ->and(BannerSection::first()->name)->toBe('Ada Lovelace')
        ->and(Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->count())->toBe(3);
});
