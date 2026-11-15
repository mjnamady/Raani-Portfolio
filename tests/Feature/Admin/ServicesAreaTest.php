<?php

use App\Models\Service;
use App\Models\User;
use App\Support\FontAwesomeIcons;
use Database\Seeders\ServiceSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->seed(ServiceSeeder::class);
});

/**
 * The editor's current rows, as the form submits them.
 *
 * @return array<int, array<string, mixed>>
 */
function currentServiceRows(): array
{
    return Service::query()->ordered()->get()->map(fn (Service $service) => [
        'id' => $service->id,
        'title' => $service->title,
        'icon' => $service->icon,
        'project_count' => $service->project_count,
        'project_count_label' => $service->project_count_label,
        'is_active' => $service->is_active ? '1' : '0',
    ])->all();
}

function newServiceRow(array $overrides = []): array
{
    return array_merge([
        'title' => 'Mobile Apps',
        'icon' => 'fa-light fa-mobile-screen',
        'project_count' => '57',
        'project_count_label' => 'Apps shipped',
        'is_active' => '1',
    ], $overrides);
}

// ---------- Access ----------

test('guests are redirected from the services editor', function () {
    $this->get(route('admin.services'))->assertRedirect(route('login'));
    $this->put(route('admin.services.update'), ['services' => []])->assertRedirect(route('login'));
});

test('non-admin users can not view or update the services', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.services'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.services.update'), ['services' => []])->assertForbidden();

    expect(Service::count())->toBe(4);
});

// ---------- Editor page ----------

test('the editor lists the current service cards from the database', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.services'))
        ->assertOk()
        ->assertSeeInOrder(['value="Web Design"', 'value="Ui/Ux Design"', 'value="Web Research"', 'value="Marketing"'], false)
        ->assertSee('value="fa-light fa-pen-ruler"', false)
        ->assertSee('value="331"', false)
        ->assertSee(asset('frontend/assets/css/vendor/fontawesome.css'));
});

// ---------- Home page ----------

test('the home page shows the seeded service cards instead of the theme placeholders', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Web Design', 'Ui/Ux Design', 'Web Research', 'Marketing'])
        ->assertSee('<i class="fa-light fa-pen-ruler" aria-hidden="true"></i>', false)
        ->assertSee('331 Projects')
        ->assertDontSee('331 Prodect');
});

test('the services section is hidden when there are no active cards', function () {
    Service::query()->update(['is_active' => false]);
    Service::forgetCache();

    $this->get('/')->assertOk()->assertDontSee('id="services"', false);
});

// ---------- Saving ----------

test('admin can edit, reorder, hide, add and remove service cards in one save', function () {
    [$webDesign, $uiux, $research, $marketing] = currentServiceRows();

    $webDesign['title'] = 'Website Design';
    $webDesign['project_count'] = '150';
    $research['is_active'] = '0';

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [
            $uiux,
            $webDesign,
            $research,
            newServiceRow(),
            // Marketing left out: removed in the editor.
        ]])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.services'))
        ->assertSessionHas('status');

    $services = Service::query()->featured()->ordered()->get();

    expect($services->pluck('title')->all())->toBe(['Ui/Ux Design', 'Website Design', 'Web Research', 'Mobile Apps'])
        ->and($services->pluck('sort_order')->all())->toBe([1, 2, 3, 4])
        ->and(Service::find($webDesign['id'])->project_count)->toBe(150)
        ->and(Service::find($research['id'])->is_active)->toBeFalse()
        ->and(Service::find($marketing['id'])->is_featured)->toBeFalse()
        ->and(Service::where('title', 'Mobile Apps')->first()->only(['icon', 'project_count', 'project_count_label', 'slug']))
        ->toBe(['icon' => 'fa-light fa-mobile-screen', 'project_count' => 57, 'project_count_label' => 'Apps shipped', 'slug' => 'mobile-apps']);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Ui/Ux Design', 'Website Design', 'Mobile Apps'])
        ->assertSee('57 Apps shipped')
        ->assertDontSee('>Web Research</a>', false)
        ->assertDontSee('>Marketing</a>', false);
});

test('saving clears the cached home page cards', function () {
    $this->get('/')->assertSee('Web Design');

    $rows = currentServiceRows();
    $rows[0]['title'] = 'Brand Identity';

    $this->actingAs($this->admin)->put(route('admin.services.update'), ['services' => $rows]);

    $this->get('/')->assertSee('Brand Identity')->assertDontSee('>Web Design<', false);
});

test('the cached cards survive a serializing cache store with class unserialization disabled', function () {
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('Web Design');
    $this->get('/')->assertOk()->assertSee('Web Design')->assertSee('fa-light fa-bezier-curve', false);
});

test('removing every card takes them off the home page but keeps the services', function () {
    // What the editor sends once every card is removed: only the empty "services" field.
    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => ''])
        ->assertSessionHasNoErrors();

    expect(Service::count())->toBe(4)
        ->and(Service::query()->featured()->count())->toBe(0);

    $this->get('/')->assertOk()->assertDontSee('id="services"', false);
    $this->actingAs($this->admin)->get(route('admin.services'))->assertDontSee('value="Web Design"', false);
});

test('a removed card keeps its service row, slug and content', function () {
    [$webDesign] = currentServiceRows();
    Service::find($webDesign['id'])->update(['content' => '<p>Full service page</p>']);

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => array_slice(currentServiceRows(), 1)])
        ->assertSessionHasNoErrors();

    expect(Service::find($webDesign['id'])->only(['slug', 'content', 'is_featured']))
        ->toBe(['slug' => 'web-design', 'content' => '<p>Full service page</p>', 'is_featured' => false]);
});

test('a request without the services field is rejected and removes nothing', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), [])
        ->assertSessionHasErrors('services');

    expect(Service::query()->featured()->count())->toBe(4);
});

test('the editor can not update or feature a service that is not on the home page', function () {
    $rows = currentServiceRows();
    $hidden = Service::factory()->create(['title' => 'Private Service', 'is_featured' => false]);

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [
            ...$rows,
            newServiceRow(['id' => $hidden->id, 'title' => 'Hijacked']),
        ]])
        ->assertSessionHasErrors('services.4.id');

    expect($hidden->fresh()->only(['title', 'is_featured']))->toBe(['title' => 'Private Service', 'is_featured' => false]);
});

test('the editor always sends the services field', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.services'))
        ->assertSee('<input type="hidden" name="services" value="">', false);
});

test('new services get unique slugs, and renaming keeps the slug', function () {
    $rows = currentServiceRows();
    $rows[0]['title'] = 'Renamed Card';
    $rows[] = newServiceRow(['title' => 'Web Research']);

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => $rows])
        ->assertSessionHasNoErrors();

    expect(Service::find($rows[0]['id'])->slug)->toBe('web-design')
        ->and(Service::where('title', 'Web Research')->pluck('slug')->sort()->values()->all())->toBe(['web-research', 'web-research-2']);
});

test('service text is escaped on the home page', function () {
    $rows = currentServiceRows();
    $rows[0]['title'] = '<script>alert(1)</script>';
    $rows[0]['project_count_label'] = '<b>x</b>';

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => $rows])
        ->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<b>x</b>', false);
});

// ---------- Validation ----------

test('service rows are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [5 => newServiceRow($overrides)]])
        ->assertSessionHasErrors("services.5.{$field}");

    expect(Service::count())->toBe(4);
})->with([
    'title missing' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 81)], 'title'],
    'icon missing' => [['icon' => ''], 'icon'],
    'icon with an unknown name' => [['icon' => 'fa-light fa-does-not-exist'], 'icon'],
    'icon with an unknown style' => [['icon' => 'fa-glow fa-pen-ruler'], 'icon'],
    'icon with extra classes' => [['icon' => 'fa-light fa-pen-ruler text-danger'], 'icon'],
    'icon with markup' => [['icon' => 'fa-light fa-pen-ruler" onmouseover="alert(1)'], 'icon'],
    'count not a number' => [['project_count' => 'many'], 'project_count'],
    'count negative' => [['project_count' => '-3'], 'project_count'],
    'count too large' => [['project_count' => '10000000'], 'project_count'],
    'label missing' => [['project_count_label' => ''], 'project_count_label'],
    'label too long' => [['project_count_label' => str_repeat('a', 31)], 'project_count_label'],
]);

test('extra spaces in the icon class are tidied before validation', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [newServiceRow(['icon' => '  fa-solid   fa-code '])]])
        ->assertSessionHasNoErrors();

    expect(Service::query()->featured()->sole()->icon)->toBe('fa-solid fa-code');
});

test('no more than eight cards are accepted', function () {
    $rows = array_map(fn ($i) => newServiceRow(['title' => "Service {$i}"]), range(1, 9));

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => $rows])
        ->assertSessionHasErrors('services');

    expect(Service::count())->toBe(4);
});

test('unknown or duplicated service ids are rejected', function () {
    $rows = currentServiceRows();

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [newServiceRow(['id' => 99999])]])
        ->assertSessionHasErrors('services.0.id');

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => [$rows[0], $rows[0]]])
        ->assertSessionHasErrors('services.0.id');

    expect(Service::count())->toBe(4);
});

test('a failed save changes nothing', function () {
    $rows = currentServiceRows();
    $rows[0]['title'] = 'Should not stick';
    $rows[] = newServiceRow(['title' => '']);

    $this->actingAs($this->admin)
        ->put(route('admin.services.update'), ['services' => $rows])
        ->assertSessionHasErrors('services.4.title');

    expect(Service::count())->toBe(4)
        ->and(Service::where('title', 'Should not stick')->exists())->toBeFalse();
});

// ---------- Icon list ----------

test('the icon list is read from the theme stylesheet', function () {
    expect(FontAwesomeIcons::names())->toContain('pen-ruler', 'bezier-curve', 'lightbulb', 'envelope')
        ->and(count(FontAwesomeIcons::names()))->toBeGreaterThan(1000)
        ->and(FontAwesomeIcons::isValid('fa-brands fa-github'))->toBeTrue()
        ->and(FontAwesomeIcons::isValid('fa-light fa-nope-not-real'))->toBeFalse();
});

test('the seeder recreates the theme cards once and never overwrites edits', function () {
    $rows = currentServiceRows();
    $rows[0]['title'] = 'Edited';
    $this->actingAs($this->admin)->put(route('admin.services.update'), ['services' => [$rows[0]]]);

    $this->seed(ServiceSeeder::class);

    expect(Service::count())->toBe(4)
        ->and(Service::query()->featured()->pluck('title')->all())->toBe(['Edited']);
});
