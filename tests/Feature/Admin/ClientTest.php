<?php

use App\Http\Requests\Admin\StoreClientRequest;
use App\Models\Client;
use App\Models\Section;
use App\Models\User;
use App\Services\ImageUploadService;
use Database\Seeders\ClientSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->seed(ClientSeeder::class);
});

function clientPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Acme Corporation',
        'url' => 'https://acme.example',
        'is_active' => '1',
        'logo' => UploadedFile::fake()->image('acme.png', 800, 200),
    ], $overrides);
}

/**
 * A wide PNG with a transparent background, like a real logo export.
 */
function transparentLogo(int $width, int $height): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledrectangle($image, intdiv($width, 4), intdiv($height, 4), intdiv($width * 3, 4), intdiv($height * 3, 4), imagecolorallocate($image, 200, 200, 200));

    ob_start();
    imagepng($image);

    return UploadedFile::fake()->createWithContent('logo.png', (string) ob_get_clean());
}

function clientNames(): array
{
    return Client::query()->ordered()->pluck('name')->all();
}

// ---------- Access ----------

test('guests are redirected from every client route', function () {
    $client = Client::query()->first();

    $this->get(route('admin.clients.index'))->assertRedirect(route('login'));
    $this->get(route('admin.clients.create'))->assertRedirect(route('login'));
    $this->post(route('admin.clients.store'), clientPayload())->assertRedirect(route('login'));
    $this->get(route('admin.clients.edit', $client))->assertRedirect(route('login'));
    $this->put(route('admin.clients.update', $client), clientPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.clients.destroy', $client))->assertRedirect(route('login'));
    $this->patch(route('admin.clients.toggle', $client))->assertRedirect(route('login'));
    $this->patch(route('admin.clients.move', $client), ['direction' => 'right'])->assertRedirect(route('login'));
    $this->put(route('admin.clients.settings'), ['is_active' => '0'])->assertRedirect(route('login'));
});

test('non-admin users can not view or change logos', function () {
    $user = User::factory()->create();
    $client = Client::query()->first();

    $this->actingAs($user)->get(route('admin.clients.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.clients.store'), clientPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.clients.update', $client), clientPayload())->assertForbidden();
    $this->actingAs($user)->delete(route('admin.clients.destroy', $client))->assertForbidden();

    expect(Client::count())->toBe(8);
});

test('the policy only allows the admin', function () {
    $client = Client::query()->first();
    $user = User::factory()->create();

    expect($this->admin->can('update', $client))->toBeTrue()
        ->and($user->can('update', $client))->toBeFalse()
        ->and($user->can('create', Client::class))->toBeFalse();
});

// ---------- Pages ----------

test('the logo wall lists the logos in order', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.clients.index'))
        ->assertOk()
        ->assertSeeInOrder(['Partner company 1', 'Partner company 2', 'Partner company 8'])
        ->assertSee(asset(Client::THEME_LOGO_DIRECTORY.'company-logo-1.svg'))
        ->assertSee('data-confirm="Delete the', false);
});

test('the sidebar links to the logo wall', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertSee('href="'.route('admin.clients.index').'"', false)
        ->assertSee('Supported Companies');
});

test('the home page shows the seeded theme logos', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('our-supported-company-area', false)
        ->assertSee(asset(Client::THEME_LOGO_DIRECTORY.'company-logo-8.svg'))
        ->assertSee('alt="Partner company 1"', false);
});

// ---------- Create, update, delete ----------

test('admin can add a logo; it is scaled to fit, stored as WebP and shown last', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.clients.index'))
        ->assertSessionHas('status');

    $client = Client::query()->ordered()->get()->last();

    expect($client->only(['name', 'url', 'sort_order']))->toBe(['name' => 'Acme Corporation', 'url' => 'https://acme.example', 'sort_order' => 9])
        ->and($client->logo)->toMatch('#^clients/[0-9a-f-]{36}\.webp$#');

    Storage::disk('public')->assertExists($client->logo);

    // 800x200 fits a 400x160 box at 400x100: shrunk, proportions kept, nothing cropped.
    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($client->logo));
    expect([$width, $height])->toBe([400, 100]);

    $this->get('/')
        ->assertSee(Storage::disk('public')->url($client->logo))
        ->assertSee('href="https://acme.example" target="_blank" rel="noopener nofollow"', false);
});

test('a small logo is never enlarged', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['logo' => UploadedFile::fake()->image('small.png', 150, 50)]))
        ->assertSessionHasNoErrors();

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get(Client::query()->latest('id')->first()->logo));

    expect([$width, $height])->toBe([150, 50]);
});

test('a tall logo is fitted by its height', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['logo' => UploadedFile::fake()->image('tall.png', 400, 800)]))
        ->assertSessionHasNoErrors();

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get(Client::query()->latest('id')->first()->logo));

    expect([$width, $height])->toBe([80, 160]);
});

test('a transparent logo keeps its transparency', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['logo' => transparentLogo(600, 200)]))
        ->assertSessionHasNoErrors();

    $stored = imagecreatefromstring(Storage::disk('public')->get(Client::query()->latest('id')->first()->logo));
    $corner = imagecolorsforindex($stored, imagecolorat($stored, 0, 0));

    expect($corner['alpha'])->toBeGreaterThan(100);
});

test('a logo without a website is shown without a link', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['name' => 'Unlinked Co', 'url' => '']))
        ->assertSessionHasNoErrors();

    $client = Client::query()->latest('id')->first();

    expect($client->url)->toBeNull();

    $html = $this->get('/')->getContent();
    expect($html)->toContain('alt="Unlinked Co"')
        ->not->toMatch('#<a [^>]*>\s*<img[^>]+alt="Unlinked Co"#');
});

test('admin can edit a logo without replacing the image', function () {
    $client = Client::query()->ordered()->first();

    $this->actingAs($this->admin)->get(route('admin.clients.edit', $client))
        ->assertOk()
        ->assertSee('value="Partner company 1"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.clients.update', $client), clientPayload(['name' => 'Globex', 'logo' => null]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.clients.index'));

    expect($client->fresh()->only(['name', 'logo']))->toBe(['name' => 'Globex', 'logo' => $client->logo]);
});

test('replacing an uploaded logo deletes the old file', function () {
    $this->actingAs($this->admin)->post(route('admin.clients.store'), clientPayload());
    $client = Client::query()->latest('id')->first();
    $first = $client->logo;

    $this->actingAs($this->admin)->put(route('admin.clients.update', $client), clientPayload([
        'logo' => UploadedFile::fake()->image('new.png', 600, 200),
    ]))->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($client->fresh()->logo);
});

test('replacing or deleting a theme logo never touches the theme files', function () {
    $client = Client::query()->ordered()->first();
    $themeFile = public_path($client->logo);

    $this->actingAs($this->admin)->put(route('admin.clients.update', $client), clientPayload())->assertSessionHasNoErrors();

    expect(file_exists($themeFile))->toBeTrue()
        ->and($client->fresh()->hasThemeLogo())->toBeFalse();

    $other = Client::query()->ordered()->skip(1)->first();
    $this->actingAs($this->admin)->delete(route('admin.clients.destroy', $other));

    expect(file_exists(public_path($other->logo)))->toBeTrue();
});

test('admin can delete a logo and its uploaded file', function () {
    $this->actingAs($this->admin)->post(route('admin.clients.store'), clientPayload());
    $client = Client::query()->latest('id')->first();

    $this->actingAs($this->admin)
        ->delete(route('admin.clients.destroy', $client))
        ->assertRedirect(route('admin.clients.index'))
        ->assertSessionHas('status');

    expect(Client::find($client->id))->toBeNull();
    Storage::disk('public')->assertMissing($client->logo);
});

// ---------- Visibility and order ----------

test('hiding a logo removes it from the home page but keeps it in the admin', function () {
    $client = Client::query()->ordered()->first();

    $this->actingAs($this->admin)->patch(route('admin.clients.toggle', $client))->assertSessionHas('status');

    expect($client->fresh()->is_active)->toBeFalse();

    $this->get('/')->assertDontSee('alt="Partner company 1"', false);
    $this->actingAs($this->admin)->get(route('admin.clients.index'))->assertSee('Partner company 1')->assertSee('Hidden');
});

test('logos can be moved left and right', function () {
    $second = Client::query()->ordered()->skip(1)->first();

    $this->actingAs($this->admin)->patch(route('admin.clients.move', $second), ['direction' => 'left']);

    expect(array_slice(clientNames(), 0, 2))->toBe(['Partner company 2', 'Partner company 1']);

    $this->actingAs($this->admin)->patch(route('admin.clients.move', $second), ['direction' => 'right']);

    expect(array_slice(clientNames(), 0, 2))->toBe(['Partner company 1', 'Partner company 2']);

    $this->actingAs($this->admin)
        ->patch(route('admin.clients.move', $second), ['direction' => 'up'])
        ->assertSessionHasErrors('direction');
});

test('the section can be switched off without losing the logos', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.clients.settings'), ['is_active' => '0'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.clients.index'));

    expect(Section::where('key', Section::HOME_CLIENTS)->value('is_active'))->toBeFalsy()
        ->and(Client::count())->toBe(8);

    $this->get('/')->assertOk()->assertDontSee('our-supported-company-area', false);
});

test('the section is hidden when there are no active logos', function () {
    Client::query()->update(['is_active' => false]);
    Client::forgetCache();

    $this->get('/')->assertOk()->assertDontSee('our-supported-company-area', false);
});

// ---------- Validation ----------

test('logo details are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Client::count())->toBe(8);
})->with([
    'name missing' => [['name' => ''], 'name'],
    'name too long' => [['name' => str_repeat('a', 81)], 'name'],
    'logo missing' => [['logo' => null], 'logo'],
    'website without a scheme' => [['url' => 'acme.example'], 'url'],
    'javascript link' => [['url' => 'javascript:alert(1)'], 'url'],
    'data link' => [['url' => 'data:text/html,<script>alert(1)</script>'], 'url'],
    'ftp link' => [['url' => 'ftp://acme.example'], 'url'],
    'website too long' => [['url' => 'https://example.com/'.str_repeat('a', 250)], 'url'],
]);

test('invalid logo files are rejected and nothing is stored', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['logo' => $file]))
        ->assertSessionHasErrors('logo');

    expect(Client::count())->toBe(8)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'svg' => fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'),
    'php renamed to png' => fn () => UploadedFile::fake()->createWithContent('shell.png', '<?php system($_GET["c"]);'),
    'pdf' => fn () => UploadedFile::fake()->create('brand.pdf', 100, 'application/pdf'),
    'gif' => fn () => UploadedFile::fake()->image('anim.gif', 400, 200),
    'too small' => fn () => UploadedFile::fake()->image('tiny.png', 40, 15),
    'too large in pixels' => fn () => UploadedFile::fake()->image('huge.png', ImageUploadService::MAX_SOURCE_DIMENSION + 1, 100),
    'over the size limit' => fn () => UploadedFile::fake()->image('big.png', 400, 200)->size(StoreClientRequest::MAX_LOGO_KILOBYTES + 1),
]);

test('SVG uploads get an explanation', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload([
            'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        ]))
        ->assertSessionHasErrors(['logo' => 'The logo must be a PNG, WebP or JPG image. SVG files are not accepted.']);
});

test('a logo that cannot be processed gives a validation error and changes nothing', function () {
    Exceptions::fake();

    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeFitted')->andThrow(new RuntimeException('Decoder failed.'));
        $mock->shouldNotReceive('delete');
    });

    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload())
        ->assertSessionHasErrors(['logo' => 'The logo could not be processed. Please try a different image.']);

    expect(Client::count())->toBe(8);
    Exceptions::assertReported(RuntimeException::class);
});

test('a failed save keeps the old logo and removes the new upload', function () {
    Exceptions::fake();

    $this->actingAs($this->admin)->post(route('admin.clients.store'), clientPayload());
    $client = Client::query()->latest('id')->first();
    $oldLogo = $client->logo;

    Client::saving(function () {
        throw new RuntimeException('Database unavailable.');
    });

    $this->actingAs($this->admin)
        ->put(route('admin.clients.update', $client), clientPayload(['logo' => UploadedFile::fake()->image('new.png', 600, 200)]))
        ->assertServerError();

    expect($client->fresh()->logo)->toBe($oldLogo)
        ->and(Storage::disk('public')->allFiles())->toBe([$oldLogo]);
});

// ---------- Home page ----------

test('saving clears the cached home page logos', function () {
    $this->get('/')->assertSee('alt="Partner company 1"', false);

    $this->actingAs($this->admin)->put(route('admin.clients.update', Client::query()->ordered()->first()), clientPayload(['name' => 'Renamed Co', 'logo' => null]));

    $this->get('/')->assertSee('alt="Renamed Co"', false)->assertDontSee('alt="Partner company 1"', false);
});

test('the cached logos survive a serializing cache store with class unserialization disabled', function () {
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('alt="Partner company 1"', false);
    $this->get('/')->assertOk()->assertSee('alt="Partner company 8"', false);
});

test('logo names and websites are escaped on the home page', function () {
    $this->actingAs($this->admin)->post(route('admin.clients.store'), clientPayload([
        'name' => '"><script>alert(1)</script>',
        'url' => 'https://acme.example/?a=1&b=2',
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertSee('href="https://acme.example/?a=1&amp;b=2"', false);
});

test('websites that try to break out of the link are rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.clients.store'), clientPayload(['url' => 'https://acme.example/?q="><script>alert(2)</script>']))
        ->assertSessionHasErrors('url');

    expect(Client::count())->toBe(8);
});

// ---------- Seeder ----------

test('the seeder recreates the demo logos once and never overwrites edits', function () {
    $this->actingAs($this->admin)->put(route('admin.clients.update', Client::query()->ordered()->first()), clientPayload(['name' => 'Edited', 'logo' => null]));

    $this->seed(ClientSeeder::class);

    expect(Client::count())->toBe(8)
        ->and(clientNames()[0])->toBe('Edited');
});
