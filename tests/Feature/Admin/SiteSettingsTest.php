<?php

use App\Models\BannerSection;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\SiteSettingsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

function siteSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'site_name' => '  Ada   Lovelace ',
        'site_tagline' => 'Designer and developer.',
        'site_copyright' => '© {year} {site}',
        'social' => ['instagram' => 'https://instagram.com/ada', 'github' => '', 'x' => '  '],
        'offcanvas_heading' => 'Hello there',
        'offcanvas_bio' => 'I build things.',
        'offcanvas_social_label' => 'Follow me',
        'offcanvas_phone_label' => 'Call',
        'offcanvas_email_label' => 'Mail',
        'offcanvas_address_label' => 'Studio',
        'footer_tagline_highlight' => 'Let us',
        'footer_tagline' => 'build together',
        'footer_links_title' => 'Links',
        'footer_contact_title' => 'Reach me',
        'footer_links' => [
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => '', 'url' => ''],
            ['label' => 'Dribbble', 'url' => 'https://dribbble.com/ada'],
        ],
    ], $overrides);
}

// ---------- Access ----------

test('guests and non-admins can not view or change the site settings', function () {
    $user = User::factory()->create();

    $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
    $this->put(route('admin.settings.update'), siteSettingsPayload())->assertRedirect(route('login'));
    $this->actingAs($user)->get(route('admin.settings.index'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.settings.update'), siteSettingsPayload())->assertForbidden();

    expect(Setting::query()->count())->toBe(0);
});

// ---------- The page ----------

test('the page shows every tab with the current values, theme images and a link to the contact details', function () {
    Setting::put('social.linkedin', 'https://linkedin.com/in/ada');

    $this->actingAs($this->admin)->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('Identity')->assertSee('Social links')->assertSee('Menu panel')->assertSee('Footer')
        ->assertSee('value="'.config('app.name').'"', false)
        ->assertSee('value="https://linkedin.com/in/ada"', false)
        ->assertSee('value="/about"', false)
        ->assertSee(asset('frontend/assets/images/logo/white-logo-reeni-2.png'), false)
        ->assertSee('href="'.route('admin.contact.index').'"', false)
        ->assertSee('href="'.route('admin.settings.index').'"', false);
});

// ---------- Saving ----------

test('the admin can save every tab', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload())
        ->assertRedirect(route('admin.settings.index'))
        ->assertSessionHas('status', 'The site settings have been updated.');

    expect(SiteSettings::group('site'))->name->toBe('Ada Lovelace')->copyright->toBe('© {year} {site}')
        ->and(SiteSettings::group('social'))->instagram->toBe('https://instagram.com/ada')->github->toBe('')->x->toBe('')
        ->and(SiteSettings::group('offcanvas'))->heading->toBe('Hello there')->address_label->toBe('Studio')
        ->and(SiteSettings::group('footer'))->links->toBe([
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => 'Dribbble', 'url' => 'https://dribbble.com/ada'],
        ])->contact_title->toBe('Reach me')
        ->and(SiteSettings::copyright())->toBe('© '.now()->year.' Ada Lovelace');
});

test('social links must be https addresses of known networks', function (array $social, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload(['social' => $social]))
        ->assertSessionHasErrors($field);

    expect(Setting::query()->count())->toBe(0);
})->with([
    'javascript' => [['instagram' => 'javascript:alert(1)'], 'social.instagram'],
    'plain http' => [['instagram' => 'http://instagram.com/ada'], 'social.instagram'],
    'data url' => [['github' => 'data:text/html,hi'], 'social.github'],
    'not a url' => [['x' => 'https://'], 'social.x'],
    'unknown network' => [['myspace' => 'https://myspace.com/ada'], 'social'],
]);

test('quick links must point to this site or an https address', function (string $url) {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload(['footer_links' => [['label' => 'Bad', 'url' => $url]]]))
        ->assertSessionHasErrors('footer_links.0.url');
})->with(['javascript:alert(1)', '//evil.example', 'http://evil.example', 'blog', '/path with space', '/\\evil.example']);

test('other fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'missing site name' => [['site_name' => ''], 'site_name'],
    'long tagline' => [['site_tagline' => str_repeat('x', 161)], 'site_tagline'],
    'missing copyright' => [['site_copyright' => ''], 'site_copyright'],
    'missing label' => [['offcanvas_phone_label' => ''], 'offcanvas_phone_label'],
    'long bio' => [['offcanvas_bio' => str_repeat('x', 301)], 'offcanvas_bio'],
    'link without text' => [['footer_links' => [['label' => '', 'url' => '/blog']]], 'footer_links.0.label'],
    'too many links' => [['footer_links' => array_fill(0, 7, ['label' => 'A', 'url' => '/a'])], 'footer_links'],
    'invalid utf-8' => [['site_name' => "Ada \xFF"], 'site_name'],
]);

test('array input gets a validation error and the page still renders', function () {
    $payload = siteSettingsPayload(['site_name' => ['x'], 'social' => 'nope', 'footer_links' => [['label' => ['x'], 'url' => '/a']]]);

    $this->actingAs($this->admin)->put(route('admin.settings.update'), $payload)
        ->assertSessionHasErrors(['site_name', 'social', 'footer_links.0.label']);

    $this->actingAs($this->admin)->followingRedirects()->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), $payload)
        ->assertOk();
});

// ---------- Images ----------

test('logos, favicon and panel photo are resized and stored as WebP', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload([
        'logo' => UploadedFile::fake()->image('logo.png', 1200, 300),
        'favicon' => UploadedFile::fake()->image('icon.png', 256, 256),
        'offcanvas_image' => UploadedFile::fake()->image('me.jpg', 800, 800),
    ]))->assertSessionHasNoErrors();

    foreach (['site.logo' => [480, 120], 'site.favicon' => [64, 64], 'offcanvas.image' => [400, 400]] as $key => [$width, $height]) {
        $path = Setting::get($key);
        Storage::disk('public')->assertExists($path);

        [$storedWidth, $storedHeight] = getimagesizefromstring(Storage::disk('public')->get($path));

        expect($path)->toEndWith('.webp')
            ->and([$storedWidth, $storedHeight])->toBe([$width, $height]);
    }

    expect(SiteSettings::imageUrl('logo'))->toBe(Storage::disk('public')->url(Setting::get('site.logo')))
        ->and(SiteSettings::imageUrl('logo_light'))->toBe(asset('frontend/assets/images/logo/logo-white.png'));
});

test('replacing or resetting an image deletes the old upload, and saving without one keeps it', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload(['logo' => UploadedFile::fake()->image('a.png', 600, 200)]));
    $first = Setting::get('site.logo');

    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload());
    expect(Setting::get('site.logo'))->toBe($first);

    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload(['logo' => UploadedFile::fake()->image('b.png', 600, 200)]));
    $second = Setting::get('site.logo');
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload(['reset_logo' => '1']));
    expect(Setting::get('site.logo'))->toBeNull()
        ->and(SiteSettings::imageUrl('logo'))->toBe(asset('frontend/assets/images/logo/white-logo-reeni-2.png'));
    Storage::disk('public')->assertMissing($second);
});

test('only files this feature stored are ever deleted, and a bad stored path never breaks the save', function (string $stored) {
    Storage::disk('public')->put('projects/cover.webp', 'x');
    Setting::put('site.logo', $stored);

    expect(SiteSettings::imageUrl('logo'))->toBe(asset('frontend/assets/images/logo/white-logo-reeni-2.png'));

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload(['logo' => UploadedFile::fake()->image('new.png', 600, 200)]))
        ->assertRedirect(route('admin.settings.index'));

    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload(['reset_logo' => '1']))
        ->assertRedirect(route('admin.settings.index'));

    Storage::disk('public')->assertExists('projects/cover.webp');
})->with(['projects/cover.webp', '../.env', 'settings/../projects/cover.webp']);

test('invisible control and direction characters are stripped from text settings', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.settings.update'), siteSettingsPayload(['site_name' => "Ada\u{202E} Love\u{200B}lace\x00"]))
        ->assertSessionHasNoErrors();

    expect(SiteSettings::group('site')['name'])->toBe('Ada Lovelace');
});

test('invalid images are refused and nothing is stored', function (string $field, Closure $file) {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload([$field => $file()]))
        ->assertSessionHasErrors($field);

    expect(Storage::disk('public')->allFiles())->toBe([])
        ->and(Setting::query()->count())->toBe(0);
})->with([
    'svg logo' => ['logo', fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')],
    'gif favicon' => ['favicon', fn () => UploadedFile::fake()->image('icon.gif', 64, 64)],
    'tiny favicon' => ['favicon', fn () => UploadedFile::fake()->image('icon.png', 16, 16)],
    'too large photo' => ['offcanvas_image', fn () => UploadedFile::fake()->image('me.jpg', 800, 800)->size(5000)],
    'not an image' => ['logo_light', fn () => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')],
]);

// ---------- Cache and seeder ----------

test('saving clears the cached settings', function () {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload());
    expect(SiteSettings::group('site')['name'])->toBe('Ada Lovelace');

    $this->actingAs($this->admin)->put(route('admin.settings.update'), siteSettingsPayload(['site_name' => 'Grace Hopper']));
    expect(SiteSettings::group('site')['name'])->toBe('Grace Hopper');
});

test('the seeder fills the defaults once, starting the panel text from the banner biography', function () {
    BannerSection::query()->create(BannerSection::defaults());

    $this->seed(SiteSettingsSeeder::class);

    expect(SiteSettings::group('offcanvas')['bio'])->toBe(BannerSection::defaults()['biography'])
        ->and(SiteSettings::socialLinks())->toBe([])
        ->and(Setting::get('footer.links'))->toHaveCount(4);

    Setting::put('site.name', 'Mine');
    $this->seed(SiteSettingsSeeder::class);

    expect(Setting::get('site.name'))->toBe('Mine');
});

test('text is escaped in the admin', function () {
    Setting::put('offcanvas.heading', '<script>alert(1)</script>');

    $this->actingAs($this->admin)->get(route('admin.settings.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});
