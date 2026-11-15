<?php

use App\Models\ResumeEntry;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\ResumeEntrySeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->seed(ResumeEntrySeeder::class);
});

function educationPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'BSc Computer Science',
        'organisation' => 'University of Lagos',
        'period' => '2012-2016',
        'description' => 'Algorithms, databases and a final-year project on accessible web apps.',
        'is_active' => '1',
    ], $overrides);
}

function educationEntries(): array
{
    return ResumeEntry::query()->education()->ordered()->pluck('title')->all();
}

// ---------- Access ----------

test('guests are redirected from every education route', function () {
    $entry = ResumeEntry::query()->education()->first();

    $this->get(route('admin.education.index'))->assertRedirect(route('login'));
    $this->get(route('admin.education.create'))->assertRedirect(route('login'));
    $this->post(route('admin.education.store'), educationPayload())->assertRedirect(route('login'));
    $this->get(route('admin.education.edit', $entry))->assertRedirect(route('login'));
    $this->put(route('admin.education.update', $entry), educationPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.education.destroy', $entry))->assertRedirect(route('login'));
    $this->patch(route('admin.education.toggle', $entry))->assertRedirect(route('login'));
    $this->patch(route('admin.education.move', $entry), ['direction' => 'down'])->assertRedirect(route('login'));
    $this->put(route('admin.education.settings'), ['title' => 'X'])->assertRedirect(route('login'));
});

test('non-admin users can not view or change education entries', function () {
    $user = User::factory()->create();
    $entry = ResumeEntry::query()->education()->first();

    $this->actingAs($user)->get(route('admin.education.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.education.store'), educationPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.education.update', $entry), educationPayload())->assertForbidden();
    $this->actingAs($user)->delete(route('admin.education.destroy', $entry))->assertForbidden();

    expect(ResumeEntry::query()->education()->count())->toBe(4);
});

test('the policy only allows the admin', function () {
    $entry = ResumeEntry::query()->education()->first();
    $user = User::factory()->create();

    expect($this->admin->can('update', $entry))->toBeTrue()
        ->and($user->can('update', $entry))->toBeFalse()
        ->and($user->can('create', ResumeEntry::class))->toBeFalse();
});

// ---------- Pages ----------

test('the education page lists entries in order with the section settings', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.education.index'))
        ->assertOk()
        ->assertSee('value="Education"', false)
        ->assertSeeInOrder(['2005-2009', 'Trainer Marketing', '2010-2014', 'Assistant Director'])
        ->assertSee(route('admin.education.create'));
});

test('the sidebar opens the Education/Experience menu on its pages', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.education.index'))
        ->assertSee('href="'.route('admin.education.index').'"', false)
        ->assertSee('href="'.route('admin.experience.index').'"', false)
        ->assertSee('collapse submenu list-unstyled show', false);
});

test('the home page shows the seeded education entries', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Education', 'Trainer Marketing', '2005-2009', 'Assistant Director', '2010-2014']);
});

// ---------- Create, update, delete ----------

test('admin can add an education entry and it appears last on the home page', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.education.store'), educationPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.education.index'))
        ->assertSessionHas('status');

    $entry = ResumeEntry::query()->education()->ordered()->get()->last();

    expect($entry->only(['type', 'title', 'organisation', 'period', 'role', 'sort_order']))->toBe([
        'type' => 'education',
        'title' => 'BSc Computer Science',
        'organisation' => 'University of Lagos',
        'period' => '2012-2016',
        'role' => null,
        'sort_order' => 5,
    ]);

    $this->get('/')->assertSee('BSc Computer Science · University of Lagos')->assertSee('2012-2016');
});

test('the institution is optional and left out of the heading when empty', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.education.store'), educationPayload(['organisation' => '']))
        ->assertSessionHasNoErrors();

    $this->get('/')->assertSee('<h4 class="edu-sub-title">BSc Computer Science</h4>', false);
});

test('admin can edit an education entry', function () {
    $entry = ResumeEntry::query()->education()->ordered()->first();

    $this->actingAs($this->admin)->get(route('admin.education.edit', $entry))
        ->assertOk()
        ->assertSee('value="Trainer Marketing"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.education.update', $entry), educationPayload(['title' => 'MSc Design']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.education.index'));

    expect($entry->fresh()->title)->toBe('MSc Design');

    $this->get('/')->assertSee('MSc Design')->assertDontSee('Trainer Marketing');
});

test('admin can delete an education entry', function () {
    $entry = ResumeEntry::query()->education()->ordered()->first();

    $this->actingAs($this->admin)
        ->delete(route('admin.education.destroy', $entry))
        ->assertRedirect(route('admin.education.index'))
        ->assertSessionHas('status');

    expect(ResumeEntry::find($entry->id))->toBeNull();

    $this->get('/')->assertDontSee('Trainer Marketing');
});

test('the delete button asks for confirmation', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.education.index'))
        ->assertSee('data-confirm="Delete', false);
});

// ---------- Visibility and order ----------

test('hiding an entry removes it from the home page but keeps it in the admin', function () {
    $entry = ResumeEntry::query()->education()->ordered()->first();

    $this->actingAs($this->admin)->patch(route('admin.education.toggle', $entry))->assertSessionHas('status');

    expect($entry->fresh()->is_active)->toBeFalse();

    $this->get('/')->assertDontSee('Trainer Marketing');
    $this->actingAs($this->admin)->get(route('admin.education.index'))->assertSee('Trainer Marketing')->assertSee('Hidden on the site');

    $this->actingAs($this->admin)->patch(route('admin.education.toggle', $entry));
    expect($entry->fresh()->is_active)->toBeTrue();
});

test('entries can be moved up and down', function () {
    $second = ResumeEntry::query()->education()->ordered()->skip(1)->first();

    $this->actingAs($this->admin)
        ->patch(route('admin.education.move', $second), ['direction' => 'up'])
        ->assertRedirect(route('admin.education.index'));

    expect(educationEntries())->toBe(['Assistant Director', 'Trainer Marketing', 'Design Assistant', 'Design Assistant']);

    $this->get('/')->assertSeeInOrder(['Assistant Director', 'Trainer Marketing']);

    // Moving the first entry up again changes nothing.
    $this->actingAs($this->admin)->patch(route('admin.education.move', $second), ['direction' => 'up']);
    expect(educationEntries()[0])->toBe('Assistant Director');
});

test('moving requires a valid direction', function () {
    $entry = ResumeEntry::query()->education()->first();

    $this->actingAs($this->admin)
        ->patch(route('admin.education.move', $entry), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

test('moving education entries never reorders experience entries', function () {
    $experienceBefore = ResumeEntry::query()->experience()->ordered()->pluck('id')->all();
    $last = ResumeEntry::query()->education()->ordered()->get()->last();

    $this->actingAs($this->admin)->patch(route('admin.education.move', $last), ['direction' => 'up']);

    expect(ResumeEntry::query()->experience()->ordered()->pluck('id')->all())->toBe($experienceBefore);
});

// ---------- Type isolation ----------

test('experience entries can not be reached through education URLs', function () {
    $experience = ResumeEntry::query()->experience()->first();

    $this->actingAs($this->admin)->get(route('admin.education.edit', $experience))->assertNotFound();
    $this->actingAs($this->admin)->put(route('admin.education.update', $experience), educationPayload())->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.education.destroy', $experience))->assertNotFound();
    $this->actingAs($this->admin)->patch(route('admin.education.toggle', $experience))->assertNotFound();
    $this->actingAs($this->admin)->patch(route('admin.education.move', $experience), ['direction' => 'up'])->assertNotFound();

    expect($experience->fresh()->type)->toBe('experience')
        ->and($experience->fresh()->is_active)->toBeTrue();
});

// ---------- Validation ----------

test('education fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.education.store'), educationPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(ResumeEntry::query()->education()->count())->toBe(4);
})->with([
    'title missing' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 81)], 'title'],
    'years missing' => [['period' => ''], 'period'],
    'years too long' => [['period' => str_repeat('1', 41)], 'period'],
    'institution too long' => [['organisation' => str_repeat('a', 121)], 'organisation'],
    'description missing' => [['description' => ''], 'description'],
    'description too long' => [['description' => str_repeat('a', 401)], 'description'],
]);

test('the type can not be changed through the form', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.education.store'), educationPayload(['type' => 'experience', 'role' => 'Injected', 'sort_order' => 99]))
        ->assertSessionHasNoErrors();

    $entry = ResumeEntry::query()->latest('id')->first();

    expect($entry->type)->toBe('education')
        ->and($entry->role)->toBeNull()
        ->and($entry->sort_order)->toBe(5);
});

// ---------- Section settings ----------

test('the education heading can be renamed and the block hidden', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.education.settings'), ['title' => 'Studies', 'is_active' => '1'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.education.index'));

    $this->get('/')->assertSee('Studies')->assertSee('Trainer Marketing');

    $this->actingAs($this->admin)->put(route('admin.education.settings'), ['title' => 'Studies', 'is_active' => '0']);

    expect(Section::where('key', Section::HOME_EDUCATION)->value('is_active'))->toBeFalsy();

    $this->get('/')->assertDontSee('Trainer Marketing')->assertSee('Fatima Asrafy');
});

test('the education settings require a title', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.education.settings'), ['title' => '', 'is_active' => '1'])
        ->assertSessionHasErrors('title');
});

// ---------- Home page ----------

test('the education block is hidden when there are no active entries', function () {
    ResumeEntry::query()->education()->update(['is_active' => false]);
    ResumeEntry::forgetCache();

    $this->get('/')->assertOk()->assertDontSee('edu-sub-title', false)->assertSee('Fatima Asrafy');
});

test('the whole section disappears when both blocks are empty', function () {
    ResumeEntry::query()->delete();

    $this->get('/')->assertOk()->assertDontSee('education-experience', false);
});

test('saving clears the cached home page entries', function () {
    $this->get('/')->assertSee('Trainer Marketing');

    $this->actingAs($this->admin)->post(route('admin.education.store'), educationPayload());

    $this->get('/')->assertSee('BSc Computer Science');
});

test('the cached entries survive a serializing cache store with class unserialization disabled', function () {
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('Trainer Marketing');
    $this->get('/')->assertOk()->assertSee('Trainer Marketing')->assertSee('Fatima Asrafy');
});

test('education text is escaped on the home page', function () {
    $this->actingAs($this->admin)->post(route('admin.education.store'), educationPayload([
        'title' => '<script>alert(1)</script>',
        'description' => '<img src=x onerror=alert(1)>',
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

// ---------- Seeder ----------

test('the seeder recreates the theme entries once and never overwrites edits', function () {
    $entry = ResumeEntry::query()->education()->ordered()->first();
    $this->actingAs($this->admin)->put(route('admin.education.update', $entry), educationPayload(['title' => 'Edited']));

    $this->seed(ResumeEntrySeeder::class);

    expect(ResumeEntry::query()->education()->count())->toBe(4)
        ->and(educationEntries()[0])->toBe('Edited');
});
