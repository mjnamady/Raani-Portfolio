<?php

use App\Models\Section;
use App\Models\SkillBar;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function aboutSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'design_title' => 'Design Tools',
        'design_active' => '1',
        'development_title' => 'Code',
        'development_active' => '1',
    ], $overrides);
}

function barNames(string $group = SkillBar::GROUP_DESIGN): array
{
    return SkillBar::query()->where('group', $group)->ordered()->pluck('name')->all();
}

// ---------- Access ----------

test('guests are redirected from every About page admin route', function () {
    $bar = SkillBar::factory()->create();

    $this->get(route('admin.about.index'))->assertRedirect(route('login'));
    $this->put(route('admin.about.settings'), aboutSettingsPayload())->assertRedirect(route('login'));
    $this->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => 'X', 'percent' => 50])->assertRedirect(route('login'));
    $this->put(route('admin.about.bars.update', $bar), ['name' => 'X', 'percent' => 50])->assertRedirect(route('login'));
    $this->patch(route('admin.about.bars.toggle', $bar))->assertRedirect(route('login'));
    $this->patch(route('admin.about.bars.move', $bar), ['direction' => 'up'])->assertRedirect(route('login'));
    $this->delete(route('admin.about.bars.destroy', $bar))->assertRedirect(route('login'));

    expect(SkillBar::query()->count())->toBe(1);
});

test('non-admin users can not view or change the About page', function () {
    $user = User::factory()->create();
    $bar = SkillBar::factory()->create(['name' => 'Untouched']);

    $this->actingAs($user)->get(route('admin.about.index'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.about.settings'), aboutSettingsPayload())->assertForbidden();
    $this->actingAs($user)->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => 'X', 'percent' => 50])->assertForbidden();
    $this->actingAs($user)->put(route('admin.about.bars.update', $bar), ['name' => 'Hacked', 'percent' => 1])->assertForbidden();
    $this->actingAs($user)->patch(route('admin.about.bars.toggle', $bar))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.about.bars.move', $bar), ['direction' => 'up'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.about.bars.destroy', $bar))->assertForbidden();

    expect($bar->fresh())->name->toBe('Untouched')->is_active->toBeTrue()
        ->and(SkillBar::query()->count())->toBe(1)
        ->and(Section::query()->count())->toBe(0);
});

test('the policy only allows the admin', function () {
    $bar = SkillBar::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $bar))->toBeTrue()
        ->and($user->can('update', $bar))->toBeFalse()
        ->and($user->can('delete', $bar))->toBeFalse()
        ->and($user->can('create', SkillBar::class))->toBeFalse();
});

// ---------- The page ----------

test('the page shows both columns with their bars, the settings and links to the reused sections', function () {
    SkillBar::factory()->create(['name' => 'Figma', 'percent' => 95]);
    SkillBar::factory()->development()->inactive()->create(['name' => 'Laravel']);

    $this->actingAs($this->admin)->get(route('admin.about.index'))
        ->assertOk()
        ->assertSee('value="Figma"', false)
        ->assertSee('value="95"', false)
        ->assertSee('value="Laravel"', false)
        ->assertSee('Hidden')
        ->assertSee('value="Design Skill"', false)
        ->assertSee('value="Development Skill"', false)
        ->assertDontSee('name="touch_title"', false)
        ->assertSee('href="'.route('admin.services').'"', false)
        ->assertSee('href="'.route('admin.counter').'"', false)
        ->assertSee('href="'.route('admin.testimonials.index').'"', false)
        ->assertSee('href="'.route('admin.contact.index').'"', false)
        ->assertSee('href="'.route('about.index').'"', false);
});

test('the sidebar links to the About page admin', function () {
    $this->actingAs($this->admin)->get(route('admin.about.index'))
        ->assertSee('href="'.route('admin.about.index').'"', false);
});

// ---------- Settings ----------

test('the admin can save the column titles and switches', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.about.settings'), aboutSettingsPayload(['development_active' => '0', 'touch_title' => 'Ignored here']))
        ->assertRedirect(route('admin.about.index'))
        ->assertSessionHas('status', 'The About page has been updated.');

    expect(Section::query()->where('key', Section::ABOUT_SKILLS_DESIGN)->first())
        ->title->toBe('Design Tools')->is_active->toBeTrue()
        ->and(Section::query()->where('key', Section::ABOUT_SKILLS_DEVELOPMENT)->first())
        ->title->toBe('Code')->is_active->toBeFalse()
        ->and(Section::query()->where('key', Section::CONTACT_GET_IN_TOUCH)->exists())->toBeFalse();
});

test('settings are validated', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.about.settings'), aboutSettingsPayload([
            'design_title' => '',
            'development_title' => str_repeat('x', 61),
        ]))
        ->assertSessionHasErrors(['design_title', 'development_title']);

    $this->actingAs($this->admin)
        ->put(route('admin.about.settings'), aboutSettingsPayload(['design_title' => ['x']]))
        ->assertSessionHasErrors(['design_title']);

    expect(Section::query()->count())->toBe(0);
});

// ---------- Skill bars ----------

test('the admin can add a bar, which goes to the end of its column', function () {
    SkillBar::factory()->create(['name' => 'Figma', 'sort_order' => 3]);
    SkillBar::factory()->development()->create(['name' => 'Laravel', 'sort_order' => 9]);

    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => '  Adobe   XD ', 'percent' => '60'])
        ->assertRedirect(route('admin.about.index'))
        ->assertSessionHas('status', 'The skill "Adobe XD" has been added.');

    $bar = SkillBar::query()->where('name', 'Adobe XD')->firstOrFail();

    expect($bar)->group->toBe('design')->percent->toBe(60)->sort_order->toBe(4)->is_active->toBeTrue()
        ->and(barNames())->toBe(['Figma', 'Adobe XD']);
});

test('the percentage must be a whole number from 0 to 100', function (mixed $percent) {
    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => 'Figma', 'percent' => $percent])
        ->assertSessionHasErrorsIn('addBarDesign', 'percent');

    expect(SkillBar::query()->count())->toBe(0);
})->with([[-1], [101], ['50.5'], ['abc'], [''], [['50']]]);

test('0 and 100 are accepted', function () {
    $this->actingAs($this->admin)->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => 'None', 'percent' => '0'])->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => 'Full', 'percent' => '100'])->assertSessionHasNoErrors();

    expect(SkillBar::query()->pluck('percent')->all())->toBe([0, 100]);
});

test('the bar name and column are validated', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => '', 'percent' => 50])
        ->assertSessionHasErrorsIn('addBarDesign', 'name');

    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => 'development', 'name' => str_repeat('x', 61), 'percent' => 50])
        ->assertSessionHasErrorsIn('addBarDevelopment', 'name');

    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => 'marketing', 'name' => 'Ads', 'percent' => 50])
        ->assertSessionHasErrorsIn('addBar', 'group');

    $this->actingAs($this->admin)
        ->post(route('admin.about.bars.store'), ['group' => ['design'], 'name' => ['x'], 'percent' => 50])
        ->assertSessionHasErrorsIn('addBar', ['group', 'name']);

    expect(SkillBar::query()->count())->toBe(0);
});

test('after array input the About page still renders', function () {
    $bar = SkillBar::factory()->create(['name' => 'Figma']);

    $this->actingAs($this->admin)->followingRedirects()->from(route('admin.about.index'))
        ->post(route('admin.about.bars.store'), ['group' => 'design', 'name' => ['x'], 'percent' => ['50']])
        ->assertOk()
        ->assertSee('value="Figma"', false);

    $this->actingAs($this->admin)->followingRedirects()->from(route('admin.about.index'))
        ->put(route('admin.about.bars.update', $bar), ['name' => ['x'], 'percent' => ['1'], 'bar_id' => [$bar->id]])
        ->assertOk();

    expect($bar->fresh()->name)->toBe('Figma');
});

test('admin changes are limited to 60 a minute, page views are not', function () {
    $bar = SkillBar::factory()->create();

    foreach (range(1, 60) as $i) {
        $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', $bar))->assertRedirect();
    }

    $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', $bar))->assertTooManyRequests();
    $this->actingAs($this->admin)->get(route('admin.about.index'))->assertOk();
});

test('the admin can rename a bar and change its percentage, but not move it to the other column', function () {
    $bar = SkillBar::factory()->create(['name' => 'Figma', 'percent' => 50]);

    $this->actingAs($this->admin)
        ->put(route('admin.about.bars.update', $bar), ['name' => 'Figma Pro', 'percent' => '90', 'group' => 'development'])
        ->assertSessionHas('status', 'The skill has been updated.');

    expect($bar->fresh())->name->toBe('Figma Pro')->percent->toBe(90)->group->toBe('design');
});

test('update errors are kept for the edited row', function () {
    $bar = SkillBar::factory()->create(['name' => 'Figma']);

    $this->actingAs($this->admin)->from(route('admin.about.index'))
        ->put(route('admin.about.bars.update', $bar), ['name' => '', 'percent' => '500', 'bar_id' => $bar->id])
        ->assertSessionHasErrorsIn('updateBar', ['name', 'percent'])
        ->assertSessionHasInput('bar_id', $bar->id);

    expect($bar->fresh()->name)->toBe('Figma');
});

test('the admin can hide, show and delete a bar', function () {
    $bar = SkillBar::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', $bar))
        ->assertSessionHas('status', 'The skill is now hidden from the About page.');
    expect($bar->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', $bar))
        ->assertSessionHas('status', 'The skill is now shown on the About page.');
    expect($bar->fresh()->is_active)->toBeTrue();

    $this->actingAs($this->admin)->delete(route('admin.about.bars.destroy', $bar))
        ->assertSessionHas('status', 'The skill has been deleted.');
    expect(SkillBar::query()->count())->toBe(0);
});

test('bars move within their own column only', function () {
    SkillBar::factory()->create(['name' => 'A', 'sort_order' => 1]);
    $b = SkillBar::factory()->create(['name' => 'B', 'sort_order' => 2]);
    SkillBar::factory()->development()->create(['name' => 'Dev', 'sort_order' => 1]);

    $this->actingAs($this->admin)->patch(route('admin.about.bars.move', $b), ['direction' => 'up']);
    expect(barNames())->toBe(['B', 'A'])
        ->and(barNames(SkillBar::GROUP_DEVELOPMENT))->toBe(['Dev']);

    $this->actingAs($this->admin)->patch(route('admin.about.bars.move', $b), ['direction' => 'up']);
    expect(barNames())->toBe(['B', 'A']);

    $this->actingAs($this->admin)->patch(route('admin.about.bars.move', $b), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

test('the delete button asks for confirmation', function () {
    SkillBar::factory()->create(['name' => 'Figma']);

    $this->actingAs($this->admin)->get(route('admin.about.index'))
        ->assertSee('data-confirm="Delete the skill &quot;Figma&quot;?"', false);
});

test('unknown bars are 404', function () {
    $this->actingAs($this->admin)->delete(route('admin.about.bars.destroy', 999))->assertNotFound();
    $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', 999))->assertNotFound();
});

test('changes clear the cached bars', function () {
    $bar = SkillBar::factory()->create(['name' => 'Before']);
    expect(SkillBar::forAboutPage()['design']->pluck('name')->all())->toBe(['Before']);

    $this->actingAs($this->admin)->put(route('admin.about.bars.update', $bar), ['name' => 'After', 'percent' => 50]);
    expect(SkillBar::forAboutPage()['design']->pluck('name')->all())->toBe(['After']);

    $this->actingAs($this->admin)->patch(route('admin.about.bars.toggle', $bar));
    expect(SkillBar::forAboutPage()['design'])->toBeEmpty();
});

test('text is escaped in the admin', function () {
    SkillBar::factory()->create(['name' => '<script>alert(1)</script>']);

    $this->actingAs($this->admin)->get(route('admin.about.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});
