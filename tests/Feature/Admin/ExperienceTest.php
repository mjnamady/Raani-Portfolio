<?php

use App\Http\Requests\Admin\UpdateResumeSectionRequest;
use App\Models\ResumeEntry;
use App\Models\Section;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Database\Seeders\ResumeEntrySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->seed(ResumeEntrySeeder::class);
});

function experiencePayload(array $overrides = []): array
{
    return array_merge([
        'organisation' => 'Acme Studio',
        'role' => 'Lead Developer',
        'period' => '2019 – Present',
        'description' => 'Leading a team of four building Laravel products for clients across Europe.',
        'is_active' => '1',
    ], $overrides);
}

function experienceSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Experiences',
        'image_alt' => 'Ada at work',
        'is_active' => '1',
    ], $overrides);
}

function experienceSection(): Section
{
    return Section::query()->where('key', Section::HOME_EXPERIENCE)->sole();
}

// ---------- Access ----------

test('guests are redirected from the experience pages', function () {
    $entry = ResumeEntry::query()->experience()->first();

    $this->get(route('admin.experience.index'))->assertRedirect(route('login'));
    $this->post(route('admin.experience.store'), experiencePayload())->assertRedirect(route('login'));
    $this->put(route('admin.experience.update', $entry), experiencePayload())->assertRedirect(route('login'));
    $this->put(route('admin.experience.settings'), experienceSettingsPayload())->assertRedirect(route('login'));
});

test('non-admin users can not view or change experience entries', function () {
    $user = User::factory()->create();
    $entry = ResumeEntry::query()->experience()->first();

    $this->actingAs($user)->get(route('admin.experience.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.experience.store'), experiencePayload())->assertForbidden();
    $this->actingAs($user)->patch(route('admin.experience.toggle', $entry))->assertForbidden();
    $this->actingAs($user)->put(route('admin.experience.settings'), experienceSettingsPayload())->assertForbidden();

    expect(ResumeEntry::query()->experience()->count())->toBe(2);
});

// ---------- Pages ----------

test('the experience page shows the photo, settings and entries', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.experience.index'))
        ->assertOk()
        ->assertSee(asset(ResumeEntry::DEFAULT_EXPERIENCE_IMAGE))
        ->assertSee('value="Experiences"', false)
        ->assertSee('Fatima Asrafy')
        ->assertSee('UI/UX Designer');
});

test('the home page shows the seeded experience entries next to the photo', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(asset(ResumeEntry::DEFAULT_EXPERIENCE_IMAGE))
        ->assertSeeInOrder(['Experiences', 'Fatima Asrafy', 'UI/UX Designer']);
});

// ---------- Create, update, delete ----------

test('admin can add an experience entry', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.experience.store'), experiencePayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.experience.index'));

    $entry = ResumeEntry::query()->experience()->ordered()->get()->last();

    expect($entry->only(['type', 'organisation', 'role', 'period', 'title', 'sort_order']))->toBe([
        'type' => 'experience',
        'organisation' => 'Acme Studio',
        'role' => 'Lead Developer',
        'period' => '2019 – Present',
        'title' => null,
        'sort_order' => 3,
    ]);

    $this->get('/')->assertSeeInOrder(['2019 – Present', 'Acme Studio', 'Lead Developer']);
});

test('an empty label shows "Experience" on the site', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.experience.store'), experiencePayload(['period' => '']))
        ->assertSessionHasNoErrors();

    $this->get('/')->assertSee('<p class="ex-subtitle">Experience</p>', false);
});

test('admin can edit and delete an experience entry', function () {
    $entry = ResumeEntry::query()->experience()->ordered()->first();

    $this->actingAs($this->admin)->get(route('admin.experience.edit', $entry))
        ->assertOk()
        ->assertSee('value="Fatima Asrafy"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.experience.update', $entry), experiencePayload(['organisation' => 'Globex']))
        ->assertSessionHasNoErrors();

    expect($entry->fresh()->organisation)->toBe('Globex');

    $this->actingAs($this->admin)->delete(route('admin.experience.destroy', $entry))->assertSessionHas('status');

    expect(ResumeEntry::find($entry->id))->toBeNull();
});

test('experience entries can be hidden and reordered', function () {
    [$first, $second] = ResumeEntry::query()->experience()->ordered()->get()->all();
    $second->update(['organisation' => 'Second Co']);

    $this->actingAs($this->admin)->patch(route('admin.experience.move', $second), ['direction' => 'up']);

    expect(ResumeEntry::query()->experience()->ordered()->pluck('id')->all())->toBe([$second->id, $first->id]);

    $this->actingAs($this->admin)->patch(route('admin.experience.toggle', $second));

    $this->get('/')->assertDontSee('Second Co');
});

test('education entries can not be reached through experience URLs', function () {
    $education = ResumeEntry::query()->education()->first();

    $this->actingAs($this->admin)->get(route('admin.experience.edit', $education))->assertNotFound();
    $this->actingAs($this->admin)->put(route('admin.experience.update', $education), experiencePayload())->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.experience.destroy', $education))->assertNotFound();

    expect(ResumeEntry::find($education->id)->type)->toBe('education');
});

// ---------- Validation ----------

test('experience fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.experience.store'), experiencePayload($overrides))
        ->assertSessionHasErrors($field);

    expect(ResumeEntry::query()->experience()->count())->toBe(2);
})->with([
    'name missing' => [['organisation' => ''], 'organisation'],
    'name too long' => [['organisation' => str_repeat('a', 121)], 'organisation'],
    'role missing' => [['role' => ''], 'role'],
    'role too long' => [['role' => str_repeat('a', 81)], 'role'],
    'label too long' => [['period' => str_repeat('a', 41)], 'period'],
    'description missing' => [['description' => ''], 'description'],
    'description too long' => [['description' => str_repeat('a', 401)], 'description'],
]);

test('experience text is escaped on the home page', function () {
    $this->actingAs($this->admin)->post(route('admin.experience.store'), experiencePayload([
        'organisation' => '<script>alert(1)</script>',
        'role' => '<b onmouseover=alert(1)>x</b>',
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<b onmouseover=alert(1)>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

// ---------- Section settings and photo ----------

test('the experience heading, alt text and visibility can be changed', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload(['title' => 'Where I worked']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.experience.index'));

    $this->get('/')->assertSee('Where I worked')->assertSee('alt="Ada at work"', false);

    $this->actingAs($this->admin)->put(route('admin.experience.settings'), experienceSettingsPayload(['is_active' => '0']));

    $this->get('/')->assertDontSee('Fatima Asrafy')->assertSee('Trainer Marketing');
});

test('the experience settings are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'title missing' => [['title' => ''], 'title'],
    'alt text missing' => [['image_alt' => ''], 'image_alt'],
]);

test('a new photo is resized, renamed and stored on the public disk', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload([
            'image' => UploadedFile::fake()->image('desk photo.jpg', 1600, 1200),
        ]))
        ->assertSessionHasNoErrors();

    $path = experienceSection()->image;

    expect($path)->toMatch('#^experience/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($path);

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect([$width, $height])->toBe([ResumeEntry::IMAGE_WIDTH, ResumeEntry::IMAGE_HEIGHT]);

    $this->get('/')->assertSee(Storage::disk('public')->url($path));
});

test('a photo wider than 4000 pixels, like a modern phone photo, is accepted and resized', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload([
            'image' => UploadedFile::fake()->image('phone.jpg', 4624, 900),
        ]))
        ->assertSessionHasNoErrors();

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get(experienceSection()->image));

    expect([$width, $height])->toBe([ResumeEntry::IMAGE_WIDTH, ResumeEntry::IMAGE_HEIGHT]);
});

test('photos above the source dimension cap or below the minimum get a clear message', function (int $width, int $height) {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload([
            'image' => UploadedFile::fake()->image('odd.jpg', $width, $height),
        ]))
        ->assertSessionHasErrors(['image' => ImageUploadService::dimensionsMessage('The image', 300)]);
})->with([
    'too small' => [200, 200],
    'too large' => [ImageUploadService::MAX_SOURCE_DIMENSION + 1, 400],
]);

test('replacing the photo deletes the old file, and saving without one keeps it', function () {
    $this->actingAs($this->admin)->put(route('admin.experience.settings'), experienceSettingsPayload([
        'image' => UploadedFile::fake()->image('first.jpg', 800, 600),
    ]));
    $first = experienceSection()->image;

    $this->actingAs($this->admin)->put(route('admin.experience.settings'), experienceSettingsPayload(['title' => 'Renamed']));
    expect(experienceSection()->image)->toBe($first);

    $this->actingAs($this->admin)->put(route('admin.experience.settings'), experienceSettingsPayload([
        'image' => UploadedFile::fake()->image('second.jpg', 800, 600),
    ]));

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists(experienceSection()->image);
});

test('invalid photos are rejected', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload(['image' => $file]))
        ->assertSessionHasErrors('image');

    expect(experienceSection()->image)->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'pdf' => fn () => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
    'php renamed to png' => fn () => UploadedFile::fake()->createWithContent('shell.png', '<?php system($_GET["c"]);'),
    'svg' => fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'),
    'larger than 4 MB' => fn () => UploadedFile::fake()->image('big.jpg', 800, 800)->size(5000),
    'too small' => fn () => UploadedFile::fake()->image('tiny.jpg', 200, 200),
]);

test('the photo limit never exceeds what PHP accepts, and the page shows it', function () {
    $limit = UploadLimit::kilobytes(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES);
    $phpLimit = (int) ini_get('upload_max_filesize') * (str_ends_with(strtolower(ini_get('upload_max_filesize')), 'g') ? 1024 * 1024 : 1024);

    expect($limit)->toBeLessThanOrEqual(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES)
        ->and($limit)->toBeLessThanOrEqual($phpLimit);

    $this->actingAs($this->admin)
        ->get(route('admin.experience.index'))
        ->assertSee('data-max-bytes="'.($limit * 1024).'"', false)
        ->assertSee('max '.UploadLimit::label(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES));
});

test('a photo above the effective limit gets a clear size message', function () {
    $limit = UploadLimit::kilobytes(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES);

    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload([
            'image' => UploadedFile::fake()->image('big.jpg', 800, 600)->size($limit + 1),
        ]))
        ->assertSessionHasErrors(['image' => 'The image may not be larger than '.UploadLimit::label(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES).'.']);

    expect(experienceSection()->image)->toBeNull();
});

test('a photo dropped by PHP before it reaches the app explains the size limit', function () {
    $path = tempnam(sys_get_temp_dir(), 'upl');
    $dropped = new UploadedFile($path, 'too-big.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload(['image' => $dropped]))
        ->assertSessionHasErrors(['image' => 'The image could not be uploaded. Please choose a file smaller than '.UploadLimit::label(UpdateResumeSectionRequest::MAX_IMAGE_KILOBYTES).'.']);

    @unlink($path);
});

test('the education settings ignore an uploaded image', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.education.settings'), [
            'title' => 'Education',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('sneaky.jpg', 800, 800),
        ])
        ->assertSessionHasNoErrors();

    expect(Section::where('key', Section::HOME_EDUCATION)->value('image'))->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('a photo that cannot be processed gives a validation error and changes nothing', function () {
    Exceptions::fake();

    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeCropped')->andThrow(new RuntimeException('Decoder failed.'));
        $mock->shouldNotReceive('delete');
    });

    $this->actingAs($this->admin)
        ->put(route('admin.experience.settings'), experienceSettingsPayload([
            'title' => 'Should not stick',
            'image' => UploadedFile::fake()->image('broken.jpg', 800, 600),
        ]))
        ->assertSessionHasErrors(['image' => 'The image could not be processed. Please try a different image.']);

    expect(experienceSection()->title)->toBe('Experiences');
    Exceptions::assertReported(RuntimeException::class);
});

// ---------- Seeder ----------

test('the seeder never overwrites edited experience entries or settings', function () {
    $this->actingAs($this->admin)->put(route('admin.experience.settings'), experienceSettingsPayload(['title' => 'Edited']));

    $this->seed(ResumeEntrySeeder::class);

    expect(ResumeEntry::query()->experience()->count())->toBe(2)
        ->and(experienceSection()->title)->toBe('Edited');
});
