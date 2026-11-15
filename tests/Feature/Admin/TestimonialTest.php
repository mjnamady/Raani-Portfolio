<?php

use App\Models\Section;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\TestimonialSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function testimonialPayload(array $overrides = []): array
{
    return array_merge([
        'quote' => 'Working together was a pleasure from start to finish.',
        'name' => 'Ada Lovelace',
        'role' => 'CTO, Analytical Engines',
        'is_active' => '1',
    ], $overrides);
}

function testimonialNames(): array
{
    return Testimonial::query()->ordered()->pluck('name')->all();
}

/**
 * Only the Testimonial section of the home page, so other sections never affect assertions.
 */
function testimonialSectionHtml(string $html): string
{
    $start = strpos($html, '<section class="testimonial-area');

    if ($start === false) {
        return '';
    }

    return substr($html, $start, strpos($html, '</section>', $start) - $start);
}

// ---------- Access ----------

test('guests are redirected from every testimonial route', function () {
    $testimonial = Testimonial::factory()->create();

    $this->get(route('admin.testimonials.index'))->assertRedirect(route('login'));
    $this->get(route('admin.testimonials.create'))->assertRedirect(route('login'));
    $this->post(route('admin.testimonials.store'), testimonialPayload())->assertRedirect(route('login'));
    $this->get(route('admin.testimonials.edit', $testimonial))->assertRedirect(route('login'));
    $this->put(route('admin.testimonials.update', $testimonial), testimonialPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.testimonials.destroy', $testimonial))->assertRedirect(route('login'));
    $this->patch(route('admin.testimonials.toggle', $testimonial))->assertRedirect(route('login'));
    $this->patch(route('admin.testimonials.move', $testimonial), ['direction' => 'up'])->assertRedirect(route('login'));
    $this->put(route('admin.testimonials.settings'), ['title' => 'X'])->assertRedirect(route('login'));

    expect(Testimonial::query()->count())->toBe(1);
});

test('non-admin users can not view or change testimonials', function () {
    $user = User::factory()->create();
    $testimonial = Testimonial::factory()->create(['name' => 'Untouched']);

    $this->actingAs($user)->get(route('admin.testimonials.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.testimonials.create'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.testimonials.store'), testimonialPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.testimonials.update', $testimonial), testimonialPayload())->assertForbidden();
    $this->actingAs($user)->patch(route('admin.testimonials.toggle', $testimonial))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.testimonials.move', $testimonial), ['direction' => 'up'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.testimonials.settings'), ['title' => 'Hacked'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.testimonials.destroy', $testimonial))->assertForbidden();

    expect($testimonial->fresh())->name->toBe('Untouched')->is_active->toBeTrue()
        ->and(Testimonial::query()->count())->toBe(1)
        ->and(Section::query()->where('key', Section::HOME_TESTIMONIALS)->exists())->toBeFalse();
});

test('the policy only allows the admin', function () {
    $testimonial = Testimonial::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $testimonial))->toBeTrue()
        ->and($this->admin->can('delete', $testimonial))->toBeTrue()
        ->and($user->can('update', $testimonial))->toBeFalse()
        ->and($user->can('delete', $testimonial))->toBeFalse()
        ->and($user->can('create', Testimonial::class))->toBeFalse();
});

// ---------- Admin pages ----------

test('the testimonials page lists every testimonial with the section settings and add links', function () {
    Testimonial::factory()->create(['name' => 'Visible Person', 'quote' => 'Great work.']);
    Testimonial::factory()->inactive()->create(['name' => 'Hidden Person']);

    $this->actingAs($this->admin)->get(route('admin.testimonials.index'))
        ->assertOk()
        ->assertSee('Visible Person')
        ->assertSee('Great work.')
        ->assertSee('Hidden Person')
        ->assertSee('Hidden')
        ->assertSee('1 of 2 shown')
        ->assertSee('value="Testimonial"', false)
        ->assertSee('href="'.route('admin.testimonials.create').'"', false)
        ->assertSee('Add testimonial');
});

test('the empty page explains the section is hidden until a testimonial is added', function () {
    $this->actingAs($this->admin)->get(route('admin.testimonials.index'))
        ->assertOk()
        ->assertSee('Add your first testimonial')
        ->assertSee('hidden on the site until you add one');
});

test('the add and edit forms render', function () {
    $testimonial = Testimonial::factory()->create(['name' => 'Grace Hopper', 'role' => 'Admiral']);

    $this->actingAs($this->admin)->get(route('admin.testimonials.create'))
        ->assertOk()
        ->assertSee('Add a testimonial')
        ->assertSee('action="'.route('admin.testimonials.store').'"', false);

    $this->actingAs($this->admin)->get(route('admin.testimonials.edit', $testimonial))
        ->assertOk()
        ->assertSee('value="Grace Hopper"', false)
        ->assertSee('value="Admiral"', false)
        ->assertSee('action="'.route('admin.testimonials.update', $testimonial).'"', false);
});

test('the sidebar links to the testimonials page', function () {
    $this->actingAs($this->admin)->get(route('admin.testimonials.index'))
        ->assertSee('href="'.route('admin.testimonials.index').'"', false);
});

// ---------- Create, update, delete ----------

test('the admin can add a testimonial, which goes to the end of the list', function () {
    Testimonial::factory()->create(['name' => 'First', 'sort_order' => 4]);

    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), testimonialPayload(['quote' => "  Line one\n\n   line two.  "]))
        ->assertRedirect(route('admin.testimonials.index'))
        ->assertSessionHas('status', 'The testimonial from Ada Lovelace has been added.');

    $testimonial = Testimonial::query()->where('name', 'Ada Lovelace')->firstOrFail();

    expect($testimonial)
        ->quote->toBe('Line one line two.')
        ->role->toBe('CTO, Analytical Engines')
        ->is_active->toBeTrue()
        ->sort_order->toBe(5)
        ->and(testimonialNames())->toBe(['First', 'Ada Lovelace']);
});

test('the role is optional and a missing checkbox means hidden', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), ['quote' => 'Nice.', 'name' => 'No Role'])
        ->assertSessionHasNoErrors();

    expect(Testimonial::query()->where('name', 'No Role')->first())
        ->role->toBeNull()
        ->is_active->toBeFalse();
});

test('the sort order can not be set through the form', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), testimonialPayload(['sort_order' => 99]));

    expect(Testimonial::query()->first()->sort_order)->toBe(1);
});

test('the admin can update a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['sort_order' => 3]);

    $this->actingAs($this->admin)
        ->put(route('admin.testimonials.update', $testimonial), testimonialPayload(['name' => 'Updated Name', 'role' => '', 'is_active' => '0']))
        ->assertRedirect(route('admin.testimonials.index'))
        ->assertSessionHas('status', 'The testimonial has been updated.');

    expect($testimonial->fresh())
        ->name->toBe('Updated Name')
        ->role->toBeNull()
        ->is_active->toBeFalse()
        ->sort_order->toBe(3);
});

test('the admin can delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.testimonials.destroy', $testimonial))
        ->assertRedirect(route('admin.testimonials.index'))
        ->assertSessionHas('status', 'The testimonial has been deleted.');

    expect(Testimonial::query()->count())->toBe(0);
});

test('the delete button asks for confirmation', function () {
    Testimonial::factory()->create(['name' => 'Confirm Me']);

    $this->actingAs($this->admin)->get(route('admin.testimonials.index'))
        ->assertSee('data-confirm="Delete the testimonial from &quot;Confirm Me&quot;? This cannot be undone."', false);
});

test('unknown testimonials are 404', function () {
    $this->actingAs($this->admin)->get(route('admin.testimonials.edit', 999))->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.testimonials.destroy', 999))->assertNotFound();
});

// ---------- Toggle and order ----------

test('the admin can hide and show a testimonial', function () {
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.testimonials.toggle', $testimonial))
        ->assertSessionHas('status', 'The testimonial is now hidden from the site.');
    expect($testimonial->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)->patch(route('admin.testimonials.toggle', $testimonial))
        ->assertSessionHas('status', 'The testimonial is now shown on the site.');
    expect($testimonial->fresh()->is_active)->toBeTrue();
});

test('the admin can move a testimonial earlier and later', function () {
    Testimonial::factory()->create(['name' => 'A', 'sort_order' => 1]);
    $b = Testimonial::factory()->create(['name' => 'B', 'sort_order' => 2]);
    Testimonial::factory()->create(['name' => 'C', 'sort_order' => 3]);

    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $b), ['direction' => 'up'])
        ->assertRedirect(route('admin.testimonials.index'));
    expect(testimonialNames())->toBe(['B', 'A', 'C']);

    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $b), ['direction' => 'down']);
    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $b), ['direction' => 'down']);
    expect(testimonialNames())->toBe(['A', 'C', 'B']);

    // Already last: nothing changes.
    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $b), ['direction' => 'down']);
    expect(testimonialNames())->toBe(['A', 'C', 'B']);
});

test('the move direction is validated', function () {
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $testimonial), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

// ---------- Validation ----------

test('required fields are validated and nothing is saved', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), ['quote' => '   ', 'name' => ''])
        ->assertSessionHasErrors(['quote', 'name']);

    expect(Testimonial::query()->count())->toBe(0);
});

test('lengths are limited', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), testimonialPayload([
            'quote' => str_repeat('a', 501),
            'name' => str_repeat('b', 81),
            'role' => str_repeat('c', 81),
        ]))
        ->assertSessionHasErrors(['quote', 'name', 'role']);

    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), testimonialPayload([
            'quote' => str_repeat('a', 500),
            'name' => str_repeat('b', 80),
            'role' => str_repeat('c', 80),
        ]))
        ->assertSessionHasNoErrors();
});

test('array input is rejected without an error page', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), testimonialPayload(['quote' => ['x'], 'name' => ['y'], 'role' => ['z']]))
        ->assertRedirect()
        ->assertSessionHasErrors(['quote', 'name', 'role']);

    // The form the admin is sent back to must render too.
    $this->actingAs($this->admin)->followingRedirects()
        ->from(route('admin.testimonials.create'))
        ->post(route('admin.testimonials.store'), testimonialPayload(['quote' => ['x'], 'name' => ['y'], 'role' => ['z']]))
        ->assertOk();

    expect(Testimonial::query()->count())->toBe(0);
});

// ---------- Section settings ----------

test('the admin can change the section title and switch it off', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.testimonials.settings'), ['title' => 'Kind Words', 'is_active' => '0'])
        ->assertRedirect(route('admin.testimonials.index'))
        ->assertSessionHas('status', 'The Testimonial section has been updated.');

    expect(Section::query()->where('key', Section::HOME_TESTIMONIALS)->first())
        ->title->toBe('Kind Words')
        ->is_active->toBeFalse();
});

test('the section title is required and short', function () {
    $this->actingAs($this->admin)->put(route('admin.testimonials.settings'), ['title' => ''])
        ->assertSessionHasErrors('title');
    $this->actingAs($this->admin)->put(route('admin.testimonials.settings'), ['title' => str_repeat('x', 41)])
        ->assertSessionHasErrors('title');
    $this->actingAs($this->admin)->put(route('admin.testimonials.settings'), ['title' => ['x']])
        ->assertSessionHasErrors('title');
});

// ---------- Home page ----------

test('the home page shows visible testimonials in order with the theme markup', function () {
    Testimonial::factory()->create(['name' => 'Second Person', 'role' => 'Designer', 'quote' => 'Second quote.', 'sort_order' => 2]);
    Testimonial::factory()->create(['name' => 'First Person', 'role' => 'Founder', 'quote' => 'First quote.', 'sort_order' => 1]);
    Testimonial::factory()->inactive()->create(['name' => 'Hidden Person', 'sort_order' => 3]);

    $section = testimonialSectionHtml($this->get('/')->assertOk()->getContent());

    expect($section)
        ->toContain('swiper-testimonials-2')
        ->toContain('<h2 class="text-doc">First quote.</h2>')
        ->toContain('<h3 class="card-title">First Person</h3>')
        ->toContain('<p class="card-para">Founder</p>')
        ->toContain('>Testimonial</h2>')
        ->not->toContain('Hidden Person')
        ->not->toContain('Cameron Williamson')
        ->and(strpos($section, 'First Person'))->toBeLessThan(strpos($section, 'Second Person'))
        ->and(substr_count($section, 'class="swiper-slide"'))->toBe(2);
});

test('an empty role leaves out the role line', function () {
    Testimonial::factory()->create(['role' => null]);

    expect(testimonialSectionHtml($this->get('/')->getContent()))->not->toContain('card-para');
});

test('the home page uses the saved section title', function () {
    Testimonial::factory()->create();
    Section::query()->create(['key' => Section::HOME_TESTIMONIALS, 'title' => 'Kind Words', 'is_active' => true]);

    expect(testimonialSectionHtml($this->get('/')->getContent()))->toContain('>Kind Words</h2>');
});

test('the section is hidden when switched off or when nothing is visible', function () {
    $this->get('/')->assertOk()->assertDontSee('testimonial-area', false);

    $testimonial = Testimonial::factory()->inactive()->create();
    $this->get('/')->assertDontSee('testimonial-area', false);

    $testimonial->update(['is_active' => true]);
    $this->get('/')->assertSee('testimonial-area', false);

    Section::query()->create(['key' => Section::HOME_TESTIMONIALS, 'title' => 'Testimonial', 'is_active' => false]);
    $this->get('/')->assertDontSee('testimonial-area', false);
});

test('changes in the admin show up on the home page right away', function () {
    $testimonial = Testimonial::factory()->create(['name' => 'Before Name']);
    $this->get('/')->assertSee('Before Name');

    $this->actingAs($this->admin)->put(route('admin.testimonials.update', $testimonial), testimonialPayload(['name' => 'After Name']));
    $this->get('/')->assertSee('After Name')->assertDontSee('Before Name');

    $this->actingAs($this->admin)->patch(route('admin.testimonials.toggle', $testimonial));
    $this->get('/')->assertDontSee('After Name');

    $this->actingAs($this->admin)->patch(route('admin.testimonials.toggle', $testimonial));
    $other = Testimonial::factory()->create(['name' => 'Other Name', 'sort_order' => 9]);
    $this->actingAs($this->admin)->patch(route('admin.testimonials.move', $other), ['direction' => 'up']);
    $section = testimonialSectionHtml($this->get('/')->getContent());
    expect(strpos($section, 'Other Name'))->toBeLessThan(strpos($section, 'After Name'));

    $this->actingAs($this->admin)->delete(route('admin.testimonials.destroy', $other));
    $this->get('/')->assertDontSee('Other Name');
});

test('text is escaped on the home page and in the admin', function () {
    Testimonial::factory()->create([
        'quote' => '<script>alert(1)</script>',
        'name' => '<img src=x onerror=alert(2)>',
        'role' => '"><b>bold</b>',
    ]);
    Section::query()->create(['key' => Section::HOME_TESTIMONIALS, 'title' => '<i>x</i>', 'is_active' => true]);

    foreach (['/', route('admin.testimonials.index')] as $url) {
        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertDontSee('"><b>bold</b>', false)
            ->assertDontSee('<i>x</i>', false);
    }
});

test('the decorative quote icon has an empty alt text', function () {
    Testimonial::factory()->create();

    expect(testimonialSectionHtml($this->get('/')->getContent()))
        ->toContain('quote.svg')->toContain('alt=""')
        ->not->toContain('alt="testimonial-icon"');
});

// ---------- Seeder ----------

test('the seeder recreates the theme content once and fixes the theme typo', function () {
    $this->seed(TestimonialSeeder::class);
    $this->seed(TestimonialSeeder::class);

    expect(Testimonial::query()->count())->toBe(4)
        ->and(testimonialNames()[0])->toBe('Cameron Williamson')
        ->and(Testimonial::query()->where('quote', 'like', '%skilA%')->exists())->toBeFalse()
        ->and(Section::query()->where('key', Section::HOME_TESTIMONIALS)->value('title'))->toBe('Testimonial');

    $section = testimonialSectionHtml($this->get('/')->getContent());
    expect(substr_count($section, 'class="swiper-slide"'))->toBe(4)
        ->and($section)->toContain('Leslie Alexander');
});

test('the seeder never overwrites existing testimonials', function () {
    Testimonial::factory()->create(['name' => 'My Real Client']);

    $this->seed(TestimonialSeeder::class);

    expect(testimonialNames())->toBe(['My Real Client']);
});
