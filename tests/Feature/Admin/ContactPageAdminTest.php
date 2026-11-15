<?php

use App\Models\Section;
use App\Models\Setting;
use App\Models\User;
use App\Support\ContactDetails;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'address_title' => 'Studio',
        'address_lines' => ['  12 Harbour   Street ', '', 'Lisbon, Portugal'],
        'email_title' => 'Write',
        'emails' => ['hello@example.com', ''],
        'phone_title' => 'Ring',
        'phones' => ['+351 (21) 555-0100', ''],
        'touch_eyebrow' => 'Say hello',
        'touch_title' => 'Let us work together',
        'touch_text' => '  I reply   within a day.  ',
    ], $overrides);
}

// ---------- Access ----------

test('guests and non-admins can not view or change the Contact page', function () {
    $user = User::factory()->create();

    $this->get(route('admin.contact.index'))->assertRedirect(route('login'));
    $this->put(route('admin.contact.update'), contactPayload())->assertRedirect(route('login'));
    $this->actingAs($user)->get(route('admin.contact.index'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.contact.update'), contactPayload())->assertForbidden();

    expect(Setting::query()->count())->toBe(0)
        ->and($user->can('update', Setting::class))->toBeFalse()
        ->and($this->admin->can('update', Setting::class))->toBeTrue();
});

// ---------- The page ----------

test('the page shows the saved details, the get in touch text and a link to the public page', function () {
    Setting::put('contact.emails', ['owner@example.com']);

    $this->actingAs($this->admin)->get(route('admin.contact.index'))
        ->assertOk()
        ->assertSee('value="owner@example.com"', false)
        ->assertSee('value="Address"', false)
        ->assertSee('value="Call Me"', false)
        ->assertSee('value="Elevate your brand with Me"', false)
        ->assertSee('href="'.route('contact.index').'"', false)
        ->assertSee('href="'.route('admin.contact.index').'"', false);
});

// ---------- Saving ----------

test('the admin can save every card and the get in touch text', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.contact.update'), contactPayload())
        ->assertRedirect(route('admin.contact.index'))
        ->assertSessionHas('status', 'The Contact page has been updated.');

    expect(ContactDetails::all())->toBe([
        'address_title' => 'Studio',
        'address_lines' => ['12 Harbour Street', 'Lisbon, Portugal'],
        'email_title' => 'Write',
        'emails' => ['hello@example.com'],
        'phone_title' => 'Ring',
        'phones' => ['+351 (21) 555-0100'],
    ])->and(Setting::query()->where('key', 'contact.emails')->value('group'))->toBe('contact')
        ->and(Section::query()->where('key', Section::CONTACT_GET_IN_TOUCH)->first())
        ->eyebrow->toBe('Say hello')
        ->title->toBe('Let us work together')
        ->subtitle->toBe('I reply within a day.');
});

test('a card can be emptied', function () {
    Setting::put('contact.phones', ['+1 555 0100']);

    $this->actingAs($this->admin)->put(route('admin.contact.update'), contactPayload(['phones' => ['', '']]))->assertSessionHasNoErrors();
    expect(ContactDetails::all()['phones'])->toBe([]);

    $this->actingAs($this->admin)->put(route('admin.contact.update'), collect(contactPayload())->except('emails')->all())->assertSessionHasNoErrors();
    expect(ContactDetails::all()['emails'])->toBe([]);
});

test('fields are validated', function (array $overrides, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.contact.update'), contactPayload($overrides))
        ->assertSessionHasErrors($field);

    expect(Setting::query()->count())->toBe(0);
})->with([
    'missing title' => [['email_title' => ''], 'email_title'],
    'long title' => [['phone_title' => str_repeat('x', 41)], 'phone_title'],
    'long address line' => [['address_lines' => [str_repeat('x', 121)]], 'address_lines.0'],
    'three lines' => [['address_lines' => ['a', 'b', 'c']], 'address_lines'],
    'bad email' => [['emails' => ['not-an-email']], 'emails.0'],
    'mailto parameters' => [['emails' => ['x?bcc=spy@example.com']], 'emails.0'],
    'duplicate email' => [['emails' => ['a@example.com', 'A@example.com']], 'emails.1'],
    'letters in phone' => [['phones' => ['call me']], 'phones.0'],
    'phone without digits' => [['phones' => ['+()']], 'phones.0'],
    'missing get in touch title' => [['touch_title' => ''], 'touch_title'],
    'long get in touch text' => [['touch_text' => str_repeat('z', 256)], 'touch_text'],
]);

test('array input gets a validation error and the page still renders', function () {
    $payload = contactPayload(['address_title' => ['x'], 'emails' => [['a@example.com']], 'phones' => 'not-a-list', 'touch_title' => ['y']]);

    $this->actingAs($this->admin)->put(route('admin.contact.update'), $payload)
        ->assertSessionHasErrors(['address_title', 'emails.0', 'phones', 'touch_title']);

    $this->actingAs($this->admin)->followingRedirects()->from(route('admin.contact.index'))
        ->put(route('admin.contact.update'), $payload)
        ->assertOk();

    expect(Setting::query()->count())->toBe(0);
});

test('invalid UTF-8 gets a validation error instead of being dropped or crashing', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.contact.update'), contactPayload(['address_lines' => ["Street \xFF"], 'touch_text' => "Hi \xFE"]))
        ->assertSessionHasErrors(['address_lines.0', 'touch_text']);

    expect(Setting::query()->count())->toBe(0);
});

test('saving clears the cached settings', function () {
    $this->actingAs($this->admin)->put(route('admin.contact.update'), contactPayload());
    expect(Setting::get('contact.emails'))->toBe(['hello@example.com']);

    $this->actingAs($this->admin)->put(route('admin.contact.update'), contactPayload(['emails' => ['new@example.com']]));
    expect(Setting::get('contact.emails'))->toBe(['new@example.com']);
});

test('text is escaped in the admin', function () {
    Setting::put('contact.address_lines', ['<script>alert(1)</script>']);

    $this->actingAs($this->admin)->get(route('admin.contact.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});
