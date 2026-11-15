<?php

use App\Models\Section;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\SkillSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function skillPayload(array $overrides = []): array
{
    return array_merge([
        'icon' => 'fa-light fa-code',
        'title' => 'Laravel Development',
        'done_count' => '48',
        'done_label' => 'Projects',
        'description' => 'Fast, well-tested Laravel applications.',
        'is_highlighted' => '0',
        'is_active' => '1',
    ], $overrides);
}

function skillTitles(): array
{
    return Skill::query()->ordered()->pluck('title')->all();
}

function highlightedTitles(): array
{
    return Skill::query()->where('is_highlighted', true)->pluck('title')->all();
}

// ---------- Access ----------

test('guests are redirected from every skill route', function () {
    $skill = Skill::factory()->create();

    $this->get(route('admin.skills.index'))->assertRedirect(route('login'));
    $this->get(route('admin.skills.create'))->assertRedirect(route('login'));
    $this->post(route('admin.skills.store'), skillPayload())->assertRedirect(route('login'));
    $this->get(route('admin.skills.edit', $skill))->assertRedirect(route('login'));
    $this->put(route('admin.skills.update', $skill), skillPayload())->assertRedirect(route('login'));
    $this->delete(route('admin.skills.destroy', $skill))->assertRedirect(route('login'));
    $this->patch(route('admin.skills.toggle', $skill))->assertRedirect(route('login'));
    $this->patch(route('admin.skills.highlight', $skill))->assertRedirect(route('login'));
    $this->patch(route('admin.skills.move', $skill), ['direction' => 'up'])->assertRedirect(route('login'));
    $this->put(route('admin.skills.settings'), ['title' => 'X'])->assertRedirect(route('login'));
});

test('non-admin users can not view or change skills', function () {
    $user = User::factory()->create();
    $skill = Skill::factory()->create(['title' => 'Untouched']);

    $this->actingAs($user)->get(route('admin.skills.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.skills.store'), skillPayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.skills.update', $skill), skillPayload())->assertForbidden();
    $this->actingAs($user)->patch(route('admin.skills.highlight', $skill))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.skills.destroy', $skill))->assertForbidden();

    expect($skill->fresh()->title)->toBe('Untouched');
});

test('the policy only allows the admin', function () {
    $skill = Skill::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $skill))->toBeTrue()
        ->and($user->can('update', $skill))->toBeFalse()
        ->and($user->can('create', Skill::class))->toBeFalse();
});

// ---------- Admin pages ----------

test('the skills page lists skills with the section settings', function () {
    $this->seed(SkillSeeder::class);

    $this->actingAs($this->admin)->get(route('admin.skills.index'))
        ->assertOk()
        ->assertSee('value="My Skill"', false)
        ->assertSeeInOrder(['UI / Visual Design', 'Product Planning', 'Motion Design'])
        ->assertSee('★ Highlighted')
        ->assertSee('data-confirm="Delete the', false);
});

test('the sidebar links to the skills page', function () {
    $this->actingAs($this->admin)->get(route('admin.banner'))
        ->assertSee('href="'.route('admin.skills.index').'"', false)
        ->assertSee('My Skill');
});

test('the form offers the icon picker and preview', function () {
    $this->actingAs($this->admin)->get(route('admin.skills.create'))
        ->assertOk()
        ->assertSee('id="sk-icon-grid"', false)
        ->assertSee('data-icon="fa-light fa-pen-ruler"', false)
        ->assertSee('id="sk-preview-card"', false)
        ->assertSee('<code>fa-light</code>', false)
        ->assertDontSee('&lt;/code&gt;', false);
});

// ---------- Create, update, delete ----------

test('admin can add a skill; the first one becomes highlighted', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.skills.store'), skillPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.skills.index'))
        ->assertSessionHas('status');

    $skill = Skill::query()->sole();

    expect($skill->only(['icon', 'title', 'done_count', 'done_label', 'is_active', 'is_highlighted', 'sort_order']))->toBe([
        'icon' => 'fa-light fa-code',
        'title' => 'Laravel Development',
        'done_count' => 48,
        'done_label' => 'Projects',
        'is_active' => true,
        'is_highlighted' => true,
        'sort_order' => 1,
    ]);
});

test('an empty count label becomes "Done"', function () {
    $this->actingAs($this->admin)->post(route('admin.skills.store'), skillPayload(['done_label' => '']))->assertSessionHasNoErrors();

    expect(Skill::query()->sole()->done_label)->toBe('Done');
});

test('admin can edit and delete a skill', function () {
    $skill = Skill::factory()->create(['title' => 'Old']);

    $this->actingAs($this->admin)->get(route('admin.skills.edit', $skill))->assertOk()->assertSee('value="Old"', false);

    $this->actingAs($this->admin)
        ->put(route('admin.skills.update', $skill), skillPayload(['title' => 'New']))
        ->assertSessionHasNoErrors();

    expect($skill->fresh()->title)->toBe('New');

    $this->actingAs($this->admin)->delete(route('admin.skills.destroy', $skill))->assertSessionHas('status');

    expect(Skill::count())->toBe(0);
});

test('skills can be reordered', function () {
    $a = Skill::factory()->create(['title' => 'A', 'sort_order' => 1]);
    $b = Skill::factory()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->admin)->patch(route('admin.skills.move', $b), ['direction' => 'up']);

    expect(skillTitles())->toBe(['B', 'A']);

    $this->actingAs($this->admin)
        ->patch(route('admin.skills.move', $a), ['direction' => 'sideways'])
        ->assertSessionHasErrors('direction');
});

// ---------- Highlight rules (SKL-2) ----------

test('highlighting a skill removes the highlight from the others', function () {
    $a = Skill::factory()->highlighted()->create(['title' => 'A', 'sort_order' => 1]);
    $b = Skill::factory()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->admin)->patch(route('admin.skills.highlight', $b))->assertSessionHas('status');

    expect(highlightedTitles())->toBe(['B']);
});

test('saving a skill with the highlight switch on moves the highlight to it', function () {
    Skill::factory()->highlighted()->create(['title' => 'A', 'sort_order' => 1]);
    $b = Skill::factory()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->admin)->put(route('admin.skills.update', $b), skillPayload(['title' => 'B', 'is_highlighted' => '1']));

    expect(highlightedTitles())->toBe(['B']);
});

test('hiding the highlighted skill passes the highlight to the first visible one', function () {
    $a = Skill::factory()->highlighted()->create(['title' => 'A', 'sort_order' => 1]);
    Skill::factory()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->admin)->patch(route('admin.skills.toggle', $a));

    expect($a->fresh()->is_active)->toBeFalse()
        ->and(highlightedTitles())->toBe(['B']);
});

test('deleting the highlighted skill passes the highlight on', function () {
    $a = Skill::factory()->highlighted()->create(['title' => 'A', 'sort_order' => 1]);
    Skill::factory()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($this->admin)->delete(route('admin.skills.destroy', $a));

    expect(highlightedTitles())->toBe(['B']);
});

test('a hidden skill can not be highlighted', function () {
    Skill::factory()->highlighted()->create(['title' => 'A']);
    $hidden = Skill::factory()->inactive()->create(['title' => 'Hidden']);

    $this->actingAs($this->admin)->patch(route('admin.skills.highlight', $hidden))->assertSessionHas('error');

    expect(highlightedTitles())->toBe(['A']);
});

// ---------- Validation ----------

test('skill fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->post(route('admin.skills.store'), skillPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Skill::count())->toBe(0);
})->with([
    'title missing' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 81)], 'title'],
    'count missing' => [['done_count' => ''], 'done_count'],
    'count negative' => [['done_count' => '-1'], 'done_count'],
    'count not whole' => [['done_count' => '2.5'], 'done_count'],
    'count too large' => [['done_count' => '10000000'], 'done_count'],
    'label too long' => [['done_label' => str_repeat('a', 31)], 'done_label'],
    'description missing' => [['description' => ''], 'description'],
    'description too long' => [['description' => str_repeat('a', 301)], 'description'],
    'icon missing' => [['icon' => ''], 'icon'],
    'unknown icon' => [['icon' => 'fa-light fa-not-a-real-icon'], 'icon'],
    'unknown style' => [['icon' => 'fa-glow fa-code'], 'icon'],
    'extra classes' => [['icon' => 'fa-light fa-code text-danger'], 'icon'],
    'markup in icon' => [['icon' => 'fa-light fa-code" onmouseover="alert(1)'], 'icon'],
    'icon as array' => [['icon' => ['fa-light fa-code']], 'icon'],
]);

test('the section settings are validated and saved', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.skills.settings'), ['eyebrow' => 'What I do', 'title' => '', 'is_active' => '1'])
        ->assertSessionHasErrors('title');

    $this->actingAs($this->admin)
        ->put(route('admin.skills.settings'), ['eyebrow' => 'What I do', 'title' => "Line one\nLine two", 'is_active' => '1'])
        ->assertSessionHasNoErrors();

    expect(Section::query()->where('key', Section::HOME_SKILLS)->value('eyebrow'))->toBe('What I do');
});

// ---------- Home page ----------

test('the home page shows the visible skills, the highlighted one as current, and no Read More links', function () {
    Skill::factory()->create(['title' => 'First Skill', 'sort_order' => 1, 'done_count' => 12, 'done_label' => 'Years']);
    Skill::factory()->highlighted()->create(['title' => 'Second Skill', 'sort_order' => 2]);
    Skill::factory()->inactive()->create(['title' => 'Hidden Skill', 'sort_order' => 3]);

    $html = $this->get('/')->assertOk()
        ->assertSeeInOrder(['First Skill', '12 Years', 'Second Skill'])
        ->assertDontSee('Hidden Skill')
        ->getContent();

    // Other (still static) home sections use "read-more-btn" too, so only look inside My Skill.
    preg_match('#<section class="my-skill.*?</section>#s', $html, $match);
    $section = $match[0] ?? '';

    expect($section)->not->toContain('read-more-btn')
        ->and(substr_count($section, 'service-item current'))->toBe(1)
        ->and($section)->toMatch('#service-item current.*?Second Skill#s')
        ->and($section)->not->toMatch('#service-item current.*?First Skill#s');
});

test('the first skill is current when none is highlighted', function () {
    Skill::factory()->create(['title' => 'Only Skill']);

    $this->get('/')->assertSee('service-item current', false);
});

test('the heading uses the saved eyebrow and line breaks', function () {
    Skill::factory()->create();
    Section::query()->create(['key' => Section::HOME_SKILLS, 'eyebrow' => 'What I do', 'title' => "Line one\nLine two", 'is_active' => true]);

    $this->get('/')->assertSee('What I do')->assertSeeInOrder(['Line one', '<br>', 'Line two'], false);
});

test('an emptied small heading stays hidden', function () {
    Skill::factory()->create();
    Section::query()->create(['key' => Section::HOME_SKILLS, 'eyebrow' => null, 'title' => 'Title only', 'is_active' => true]);

    $html = $this->get('/')->getContent();
    preg_match('#<section class="my-skill.*?</section>#s', $html, $match);

    expect($match[0] ?? '')->toContain('Title only')->not->toContain('section-sub-title');
});

test('the section hides when switched off or empty, and the portfolio wrapper stays', function () {
    $this->get('/')->assertOk()->assertDontSee('class="my-skill', false)->assertSee('tpm-custom-box-bg', false);

    Skill::factory()->create(['title' => 'Some Skill']);
    Section::query()->create(['key' => Section::HOME_SKILLS, 'title' => 'T', 'is_active' => false]);

    $this->get('/')->assertOk()->assertDontSee('Some Skill');
});

test('changes show up on the home page right away', function () {
    $skill = Skill::factory()->create(['title' => 'Before']);
    $this->get('/')->assertSee('Before');

    $this->actingAs($this->admin)->put(route('admin.skills.update', $skill), skillPayload(['title' => 'After']));

    $this->get('/')->assertSee('After')->assertDontSee('Before');
});

test('the cached skills survive a serializing cache store', function () {
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);
    Skill::factory()->create(['title' => 'Cached Skill']);

    $this->get('/')->assertOk()->assertSee('Cached Skill');
    $this->get('/')->assertOk()->assertSee('Cached Skill');
});

test('skill text is escaped on the home page', function () {
    Skill::factory()->create([
        'title' => '<script>alert(1)</script>',
        'description' => '<img src=x onerror=alert(2)>',
        'done_label' => '<b>x</b>',
    ]);

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<b>x</b>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

// ---------- Seeder ----------

test('the seeder creates the demo skills once, with one highlighted, and never overwrites edits', function () {
    $this->seed(SkillSeeder::class);
    Skill::query()->ordered()->first()->update(['title' => 'Edited']);

    $this->seed(SkillSeeder::class);

    expect(Skill::count())->toBe(3)
        ->and(skillTitles()[0])->toBe('Edited')
        ->and(Skill::query()->where('is_highlighted', true)->count())->toBe(1)
        ->and(Skill::query()->pluck('description')->implode(' '))->not->toContain('[Main Areas');
});
