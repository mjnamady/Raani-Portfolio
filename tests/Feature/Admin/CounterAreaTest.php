<?php

use App\Models\Section;
use App\Models\Stat;
use App\Models\User;
use Database\Seeders\BannerSectionSeeder;
use Database\Seeders\CounterAreaSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->seed(CounterAreaSeeder::class);
});

function counterAreaPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'is_active' => '1',
        'highlight' => [
            'value' => '12',
            'suffix' => '+',
            'label' => "Years in\nsoftware",
            'description' => 'Shipping products for startups and agencies since 2014.',
        ],
        'counters' => [
            ['value' => '150', 'suffix' => '+', 'label' => 'Projects shipped'],
            ['value' => '40', 'suffix' => '', 'label' => 'Happy clients'],
            ['value' => '9', 'suffix' => 'k', 'label' => 'Commits this year'],
            ['value' => '3', 'suffix' => '', 'label' => 'Open source tools'],
        ],
    ], $overrides);
}

// ---------- Access ----------

test('guests are redirected from the counter area editor', function () {
    $this->get(route('admin.counter'))->assertRedirect(route('login'));
    $this->put(route('admin.counter.update'), counterAreaPayload())->assertRedirect(route('login'));
});

test('non-admin users can not view or update the counter area', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.counter'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.counter.update'), counterAreaPayload())->assertForbidden();

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_HIGHLIGHT)->first()->value)->toBe(25);
});

// ---------- Editor page ----------

test('the editor shows the current counter area content from the database', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.counter'))
        ->assertOk()
        ->assertSee('value="25"', false)
        ->assertSee("Years Of\nexperience")
        ->assertSee('value="Our Project Complete"', false)
        ->assertSee('value="1000"', false)
        ->assertSee('value="k+"', false)
        ->assertSee(asset('backend/custom/js/counter-area.js'));
});

test('the sidebar links to the counter area editor', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertSee('href="'.route('admin.counter').'"', false);
});

// ---------- Home page ----------

test('the home page shows the seeded counter area', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('counter-area', false)
        ->assertSee('data-count="25"', false)
        ->assertSeeInOrder(['Years Of', '<br>', 'experience'], false)
        ->assertSeeInOrder(['Our Project Complete', 'Our Natural Products', 'Clients Reviews', 'Our Satisfied Clients']);
});

// ---------- Saving content ----------

test('admin can update the counter area and the home page shows it', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.counter'))
        ->assertSessionHas('status');

    $highlight = Stat::query()->forPlacement(Stat::PLACEMENT_HIGHLIGHT)->sole();

    expect($highlight->only(['value', 'suffix', 'label', 'description']))->toBe([
        'value' => 12,
        'suffix' => '+',
        'label' => "Years in\nsoftware",
        'description' => 'Shipping products for startups and agencies since 2014.',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-count="12"', false)
        ->assertSeeInOrder(['Years in', '<br>', 'software'], false)
        ->assertSee('Shipping products for startups and agencies since 2014.')
        ->assertSeeInOrder(['Projects shipped', 'Happy clients', 'Commits this year', 'Open source tools'])
        ->assertDontSee('Our Project Complete');
});

test('counters are saved in order and replace the previous ones', function () {
    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload())->assertSessionHasNoErrors();

    $counters = Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->get();

    expect($counters->pluck('label')->all())->toBe(['Projects shipped', 'Happy clients', 'Commits this year', 'Open source tools'])
        ->and($counters->pluck('value')->all())->toBe([150, 40, 9, 3])
        ->and($counters->pluck('sort_order')->all())->toBe([1, 2, 3, 4])
        ->and(Stat::query()->forPlacement(Stat::PLACEMENT_HIGHLIGHT)->count())->toBe(1);
});

test('saving the counter area leaves banner stats untouched', function () {
    $this->seed(BannerSectionSeeder::class);

    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload())->assertSessionHasNoErrors();

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->pluck('label')->all())
        ->toBe(['Winning award', 'Complete project', 'Client review']);
});

test('saving clears the cached home page counter area', function () {
    $this->get('/')->assertSee('Our Project Complete');

    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload());

    $this->get('/')->assertSee('Projects shipped')->assertDontSee('Our Project Complete');
});

test('the cached counter area survives a serializing cache store with class unserialization disabled', function () {
    // The array store used in tests never serializes; the real database store does.
    config(['cache.default' => 'database', 'cache.serializable_classes' => false]);

    $this->get('/')->assertOk()->assertSee('Our Project Complete');
    $this->get('/')->assertOk()->assertSee('Our Project Complete')->assertSee('data-count="25"', false);

    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload())->assertSessionHasNoErrors();
    auth()->logout();

    $this->get('/')->assertOk()->assertSee('Projects shipped');
    $this->get('/')->assertOk()->assertSee('Projects shipped')->assertSee('data-count="12"', false);
});

test('the counter area can be hidden without losing its content', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload(['is_active' => '0']))
        ->assertSessionHasNoErrors();

    expect(Section::query()->where('key', Section::HOME_COUNTER)->value('is_active'))->toBeFalsy()
        ->and(Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->count())->toBe(4);

    $this->get('/')->assertOk()->assertDontSee('class="counter-area"', false);

    $this->actingAs($this->admin)->get(route('admin.counter'))->assertSee('value="Projects shipped"', false);
});

test('an empty description hides the paragraph', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload(['highlight' => ['description' => '']]))
        ->assertSessionHasNoErrors();

    $this->get('/')->assertOk()->assertDontSee('year-para', false);
});

test('counter area text is escaped on the home page', function () {
    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload([
        'highlight' => ['label' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(1)>'],
        'counters' => [0 => ['label' => '<b onmouseover=alert(1)>x</b>']],
    ]))->assertSessionHasNoErrors();

    $this->get('/')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('<b onmouseover=alert(1)>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

// ---------- Validation ----------

test('highlight fields are validated', function (array $highlight, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload(['highlight' => $highlight]))
        ->assertSessionHasErrors($field);

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_HIGHLIGHT)->first()->value)->toBe(25);
})->with([
    'number missing' => [['value' => ''], 'highlight.value'],
    'number not whole' => [['value' => '2.5'], 'highlight.value'],
    'number negative' => [['value' => '-1'], 'highlight.value'],
    'number too large' => [['value' => '10000000'], 'highlight.value'],
    'suffix too long' => [['suffix' => 'abcdef'], 'highlight.suffix'],
    'label missing' => [['label' => ''], 'highlight.label'],
    'label too long' => [['label' => str_repeat('a', 61)], 'highlight.label'],
    'description too long' => [['description' => str_repeat('a', 301)], 'highlight.description'],
]);

test('a request without the highlight is rejected', function () {
    $payload = counterAreaPayload();
    unset($payload['highlight']);

    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), $payload)
        ->assertSessionHasErrors('highlight');

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->count())->toBe(4);
});

test('empty counter rows are skipped', function () {
    $this->actingAs($this->admin)->put(route('admin.counter.update'), counterAreaPayload([
        'counters' => [
            1 => ['value' => '', 'suffix' => '', 'label' => ''],
            2 => ['value' => '', 'suffix' => '', 'label' => ''],
            3 => ['value' => '', 'suffix' => '', 'label' => ''],
        ],
    ]))->assertSessionHasNoErrors();

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->pluck('label')->all())->toBe(['Projects shipped']);

    $this->get('/')->assertSee('Projects shipped')->assertDontSee('Happy clients');
});

test('a half-filled counter row reports the error on that row', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload([
            'counters' => [2 => ['value' => '50', 'suffix' => '', 'label' => '']],
        ]))
        ->assertSessionHasErrors('counters.2.label')
        ->assertSessionDoesntHaveErrors('counters.0.label');
});

test('counters are validated', function (array $row, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload(['counters' => [0 => $row]]))
        ->assertSessionHasErrors($field);

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->first()->label)->toBe('Our Project Complete');
})->with([
    'number not whole' => [['value' => 'abc'], 'counters.0.value'],
    'number too large' => [['value' => '10000000'], 'counters.0.value'],
    'suffix too long' => [['suffix' => 'abcdef'], 'counters.0.suffix'],
    'label too long' => [['label' => str_repeat('a', 61)], 'counters.0.label'],
]);

test('no more than four counters can be saved', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.counter.update'), counterAreaPayload([
            'counters' => [4 => ['value' => '1', 'suffix' => '', 'label' => 'Fifth']],
        ]))
        ->assertSessionHasErrors('counters');

    expect(Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->count())->toBe(4);
});
