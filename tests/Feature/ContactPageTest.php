<?php

use App\Models\Project;
use App\Models\Section;
use App\Models\Setting;
use App\Support\ContactDetails;
use Database\Seeders\ContactSettingsSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Only the contact cards of the Contact page.
 */
function contactCardsHtml(string $html): string
{
    $start = strpos($html, '<div class="contact-info-wrap');

    return $start === false ? '' : substr($html, $start, strpos($html, '<section class="get-in-touch-area', $start) - $start);
}

test('the cards show the saved details, each email linking to its own address and phones as tel links', function () {
    Setting::put('contact.address_title', 'Studio');
    Setting::put('contact.address_lines', ['12 Harbour Street', 'Lisbon, Portugal']);
    Setting::put('contact.emails', ['hello@example.com', 'work@example.com']);
    Setting::put('contact.phones', ['+351 (21) 555-0100', '021 555 0199']);

    $cards = contactCardsHtml($this->get(route('contact.index'))->assertOk()->getContent());

    expect($cards)
        ->toContain('Studio')
        ->toContain('12 Harbour Street')
        ->toContain('Lisbon, Portugal')
        ->toContain('<a href="mailto:hello@example.com">')
        ->toContain('<a href="mailto:work@example.com">')
        ->toContain('<a href="tel:+351215550100">')
        ->toContain('<a href="tel:0215550199">')
        ->toContain('E-mail')
        ->toContain('Call Me')
        ->toContain('col-lg-4 col-md-6');
});

test('cards without lines are hidden and the rest share the row', function () {
    Setting::put('contact.emails', ['hello@example.com']);
    Setting::put('contact.phones', ['+1 555 0100']);

    $cards = contactCardsHtml($this->get(route('contact.index'))->getContent());

    expect($cards)->not->toContain('fa-location-dot')->toContain('col-lg-6 col-md-6');

    Setting::put('contact.emails', []);
    Setting::put('contact.phones', []);

    $this->get(route('contact.index'))->assertOk()->assertDontSee('contact-info-wrap', false)->assertSee('get-in-touch-area', false);
});

test('no theme demo content or vendor link is left', function () {
    $this->seed(ContactSettingsSeeder::class);

    $html = $this->get(route('contact.index'))->assertOk()->getContent();

    expect($html)
        ->not->toContain('Dhaka 102')
        ->not->toContain('themespark11@gmail.com')
        ->not->toContain('hasan@yourmail.com')
        ->not->toContain('0000 - 000')
        ->not->toContain('ished fact')
        ->not->toContain('Appointment Now')
        ->not->toContain('inversweb.com')
        ->not->toContain('mailer.php')
        ->toContain('Send Message')
        ->toContain('<a href="'.url('/').'">Home</a>');
});

test('the get in touch text is shared with the About page', function () {
    Section::query()->create([
        'key' => Section::CONTACT_GET_IN_TOUCH,
        'eyebrow' => 'Say hello',
        'title' => 'Let us work together',
        'subtitle' => 'I reply within a day.',
    ]);

    foreach ([route('contact.index'), route('about.index')] as $url) {
        $this->get($url)->assertSee('Say hello')->assertSee('Let us work together')->assertSee('I reply within a day.');
    }
});

test('the Contact and About forms post to the inbox as contact messages', function () {
    foreach ([route('contact.index'), route('about.index')] as $url) {
        $this->get($url)
            ->assertSee('id="contact-form" method="POST" action="'.route('messages.store').'" data-message-form', false)
            ->assertSee('name="source" value="contact"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="phone"', false);
    }
});

test('text is escaped on the Contact page', function () {
    Setting::put('contact.address_title', '<b>x</b>');
    Setting::put('contact.address_lines', ['<script>alert(1)</script>']);
    Setting::put('contact.emails', ['"onmouseover="alert(2)@example.com']);

    $this->get(route('contact.index'))
        ->assertOk()
        ->assertDontSee('<b>x</b>', false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('"onmouseover="', false);
});

test('stored values of the wrong type can not break the pages', function () {
    Setting::put('contact.emails', 'oops');
    Setting::put('contact.address_title', ['x']);
    Setting::put('contact.phones', ['+1 555 0100', ['nested']]);

    $this->get(route('contact.index'))->assertOk()->assertSee('tel:+15550100', false);
    $this->get(route('about.index'))->assertOk();

    expect(ContactDetails::all())->emails->toBe([])->address_title->toBe('Address');
});

test('mailto links are encoded, whatever is stored', function () {
    Setting::put('contact.emails', ['hi?subject=Pay&body=now@example.com']);

    $this->get(route('contact.index'))->assertSee('href="mailto:hi%3Fsubject%3DPay%26body%3Dnow@example.com"', false);
});

test('a settings change inside a transaction clears the cache once it commits', function () {
    Setting::put('contact.emails', ['old@example.com']);
    expect(Setting::get('contact.emails'))->toBe(['old@example.com']);

    DB::transaction(function () {
        Setting::put('contact.emails', ['new@example.com']);
        Setting::allCached();
    });

    expect(Setting::get('contact.emails'))->toBe(['new@example.com']);
});

test('tel links keep only digits and a leading plus', function () {
    expect(ContactDetails::telHref(' +1 (555) 010-0000 '))->toBe('tel:+15550100000')
        ->and(ContactDetails::telHref('555.0100'))->toBe('tel:5550100')
        ->and(ContactDetails::telHref('+()'))->toBeNull();
});

test('the seeder fills the demo contact details once and never overwrites them', function () {
    $this->seed(ContactSettingsSeeder::class);

    expect(ContactDetails::all())
        ->address_lines->toBe(['1179 KFC Road, Lisbon, Portugal'])
        ->emails->toBe(['info@example.com'])
        ->phones->toBe(['(629) 555-0129'])
        ->email_title->toBe('E-mail')
        ->and(Section::query()->where('key', Section::CONTACT_GET_IN_TOUCH)->value('title'))->toBe('Elevate your brand with Me');

    Setting::put('contact.emails', ['mine@example.com']);
    $this->seed(ContactSettingsSeeder::class);

    expect(ContactDetails::all()['emails'])->toBe(['mine@example.com'])
        ->and(Setting::query()->count())->toBe(6);
});

test('the project details form posts to the inbox as a project message about that project', function () {
    $project = Project::factory()->published()->create(['title' => 'Harbour App']);

    $this->get(route('projects.show', $project->slug))
        ->assertOk()
        ->assertSee('action="'.route('messages.store').'" data-message-form', false)
        ->assertSee('name="source" value="project"', false)
        ->assertSee('name="project_id" value="'.$project->id.'"', false)
        ->assertSee('value="About: Harbour App"', false)
        ->assertSee('id="form-messages"', false);
});
