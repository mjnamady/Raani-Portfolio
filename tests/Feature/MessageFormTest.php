<?php

use App\Http\Controllers\Frontend\MessageController;
use App\Http\Requests\StoreMessageRequest;
use App\Mail\NewMessageMail;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->owner = User::factory()->admin()->create(['email' => 'owner@example.test']);
});

function messagePayload(array $overrides = []): array
{
    return array_merge([
        'source' => 'contact',
        'name' => '  Ada   Lovelace ',
        'email' => 'ada@example.test',
        'phone' => '+44 20 7946 0000',
        'subject' => 'A new project',
        'body' => "Hello!\r\nI would like to talk about a website.",
    ], $overrides);
}

// ---------- Saving ----------

test('a contact message is saved unread with the visitor details set on the server', function () {
    $this->from(route('contact.index'))
        ->post(route('messages.store'), messagePayload(['read_at' => now(), 'ip_address' => '1.2.3.4']))
        ->assertRedirect(route('contact.index').'#contact-form')
        ->assertSessionHas('message_status_contact', MessageController::THANK_YOU);

    expect(Message::query()->sole())
        ->source->toBe('contact')
        ->name->toBe('Ada Lovelace')
        ->body->toBe("Hello!\nI would like to talk about a website.")
        ->project_id->toBeNull()
        ->read_at->toBeNull()
        ->ip_address->toBe('127.0.0.1');
});

test('a chat message is saved as chat and sends the visitor back to the chat form', function () {
    $this->from(route('about.index'))
        ->post(route('messages.store'), ['source' => 'chat', 'name' => 'Ada', 'email' => 'ada@example.test', 'body' => 'Quick question about pricing.'])
        ->assertRedirect(route('about.index').'#chat-form')
        ->assertSessionHas('message_status_chat');

    expect(Message::query()->sole())->source->toBe('chat')->subject->toBeNull();
});

test('a project message is linked to its published project', function () {
    $project = Project::factory()->published()->create();

    $this->post(route('messages.store'), messagePayload(['source' => 'project', 'project_id' => $project->id]))->assertSessionHasNoErrors();

    expect(Message::query()->sole())->source->toBe('project')->project_id->toBe($project->id);
});

test('project messages need a published project, and other sources ignore the project', function () {
    $draft = Project::factory()->create();
    $live = Project::factory()->published()->create();

    $this->post(route('messages.store'), messagePayload(['source' => 'project', 'project_id' => $draft->id]))->assertSessionHasErrorsIn('message_project', 'project_id');
    $this->post(route('messages.store'), messagePayload(['source' => 'project']))->assertSessionHasErrorsIn('message_project', 'project_id');

    $this->post(route('messages.store'), messagePayload(['project_id' => $live->id]))->assertSessionHasNoErrors();

    expect(Message::query()->sole()->project_id)->toBeNull();
});

test('the redirect never leaves this site', function () {
    $this->withHeader('referer', 'https://evil.example/page')
        ->post(route('messages.store'), messagePayload())
        ->assertRedirect(url('/').'#contact-form');
});

// ---------- Validation ----------

test('fields are validated and errors go to the form that was used', function (array $overrides, string $field) {
    // An unknown source falls back to the contact form's error bag.
    $source = array_key_exists($overrides['source'] ?? 'contact', Message::SOURCES) ? ($overrides['source'] ?? 'contact') : 'contact';

    $this->post(route('messages.store'), messagePayload($overrides))
        ->assertSessionHasErrorsIn('message_'.$source, $field);

    expect(Message::query()->count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'long name' => [['name' => str_repeat('a', 81)], 'name'],
    'missing email' => [['email' => ''], 'email'],
    'bad email' => [['email' => 'not-an-email'], 'email'],
    'mailto parameters' => [['email' => 'x?bcc=spy@example.com'], 'email'],
    'letters in phone' => [['phone' => 'call me'], 'phone'],
    'long subject' => [['subject' => str_repeat('s', 151)], 'subject'],
    'short message' => [['body' => 'Hi'], 'body'],
    'long message' => [['body' => str_repeat('m', Message::MAX_BODY_LENGTH + 1)], 'body'],
    'unknown source' => [['source' => 'pricing'], 'source'],
    'invalid utf-8' => [['body' => "Hello there \xFF\xFE"], 'body'],
    'chat errors' => [['source' => 'chat', 'email' => ''], 'email'],
]);

test('invisible characters are stripped, so a name of only zero-width characters is refused', function () {
    $this->post(route('messages.store'), messagePayload(['name' => "\u{200B}\u{202E}"]))->assertSessionHasErrorsIn('message_contact', 'name');

    $this->post(route('messages.store'), messagePayload(['name' => "Ada\u{202E} Lovelace"]))->assertSessionHasNoErrors();

    expect(Message::query()->sole()->name)->toBe('Ada Lovelace');
});

test('array input is rejected and the page still renders', function () {
    $payload = ['source' => ['contact'], 'name' => ['x'], 'email' => ['y'], 'body' => ['z']];

    $this->post(route('messages.store'), $payload)->assertSessionHasErrorsIn('message_contact', ['source', 'name', 'email', 'body']);

    $this->from(route('contact.index'))->followingRedirects()
        ->post(route('messages.store'), $payload)
        ->assertOk();
});

test('without JavaScript the errors and the typed text come back on the page', function () {
    $this->from(route('contact.index'))->followingRedirects()
        ->post(route('messages.store'), messagePayload(['email' => 'not-an-email', 'name' => 'Ada Lovelace']))
        ->assertOk()
        ->assertSee('Please check the highlighted fields.')
        ->assertSee('The email field must be a valid email address.')
        ->assertSee('value="Ada Lovelace"', false);

    $this->from(route('contact.index'))->followingRedirects()
        ->post(route('messages.store'), messagePayload())
        ->assertSee(MessageController::THANK_YOU);
});

test('chat errors open the chat popup and stay out of the contact form', function () {
    $html = $this->from(route('contact.index'))->followingRedirects()
        ->post(route('messages.store'), ['source' => 'chat', 'name' => 'Ada', 'email' => '', 'body' => 'Hello there, quick one.'])
        ->getContent();

    expect($html)->toContain('id="click" aria-label="Open the chat form" checked')
        ->and(substr_count($html, 'Please check the highlighted fields.'))->toBe(1);
});

// ---------- JavaScript answers (CON-3) ----------

test('JavaScript requests get JSON answers', function () {
    $this->postJson(route('messages.store'), messagePayload())
        ->assertOk()
        ->assertExactJson(['message' => MessageController::THANK_YOU]);

    $this->postJson(route('messages.store'), messagePayload(['email' => 'nope']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ---------- Spam protection (CON-4) ----------

test('honeypot submissions get the normal answer but are not saved or emailed, and the drop is logged', function () {
    Log::spy();

    $this->post(route('messages.store'), messagePayload([StoreMessageRequest::HONEYPOT => 'http://spam.example']))
        ->assertSessionHas('message_status_contact', MessageController::THANK_YOU);

    $this->postJson(route('messages.store'), messagePayload([StoreMessageRequest::HONEYPOT => 'x']))->assertOk();

    expect(Message::query()->count())->toBe(0);
    Mail::assertNothingQueued();
    Log::shouldHaveReceived('info')->with('Message discarded by the honeypot.', ['form' => 'contact'])->twice();
});

test('fields a browser autofills, like "website", never cause a message to be discarded', function () {
    $this->postJson(route('messages.store'), messagePayload(['website' => 'https://ada.example', 'url' => 'https://ada.example', 'company' => 'Ada Ltd']))
        ->assertOk();

    expect(Message::query()->count())->toBe(1);
});

test('the honeypot uses a name and label browsers do not autofill, and tells password managers to skip it', function () {
    expect(StoreMessageRequest::HONEYPOT)->not->toBeIn(['website', 'url', 'company', 'name', 'email', 'phone']);

    $this->get(route('contact.index'))
        ->assertSee('name="'.StoreMessageRequest::HONEYPOT.'"', false)
        ->assertSee('Leave this field empty')
        ->assertSee('data-lpignore="true" data-1p-ignore="true" data-bwignore="true"', false)
        ->assertDontSee('>Website</label>', false);
});

test('more than 5 messages a minute from one visitor are blocked with a friendly answer', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('messages.store'), messagePayload(['body' => "Message number {$i} here."]))->assertSessionHasNoErrors();
    }

    $this->postJson(route('messages.store'), messagePayload())
        ->assertTooManyRequests()
        ->assertJson(['message' => 'You are sending messages too quickly. Please wait a little and try again.']);

    $this->from(route('contact.index'))->post(route('messages.store'), messagePayload(['body' => 'Sixth message here.']))
        ->assertRedirect(route('contact.index').'#contact-form')
        ->assertSessionHasErrorsIn('message_contact', 'body')
        ->assertSessionHasInput('body', 'Sixth message here.');

    expect(Message::query()->count())->toBe(5);

    $this->travel(61)->seconds();
    $this->post(route('messages.store'), messagePayload())->assertSessionHasNoErrors();
});

test('IPv6 visitors share one limit per /64 network', function () {
    foreach (range(1, 5) as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => "2001:db8:1:2::{$i}"])->post(route('messages.store'), messagePayload())->assertSessionHasNoErrors();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:1:2::beef'])->postJson(route('messages.store'), messagePayload())->assertTooManyRequests();
    $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:1:3::1'])->postJson(route('messages.store'), messagePayload())->assertOk();
});

test('the whole site accepts at most 100 messages an hour', function () {
    foreach (range(1, 100) as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.1.'.intdiv($i, 250).'.'.($i % 250)])->postJson(route('messages.store'), messagePayload());
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.9.9.9'])->postJson(route('messages.store'), messagePayload())->assertTooManyRequests();

    expect(Message::query()->count())->toBe(100);
});

// ---------- Owner email (CON-1) ----------

test('the owner gets a queued email that replies to the visitor', function () {
    $this->post(route('messages.store'), messagePayload());

    Mail::assertQueued(NewMessageMail::class, fn (NewMessageMail $mail) => $mail->hasTo('owner@example.test')
        && $mail->hasReplyTo('ada@example.test', 'Ada Lovelace')
        && $mail->inboxMessage->is(Message::query()->sole()));
});

test('the email escapes the visitor text and links to the inbox', function () {
    $message = Message::factory()->create(['name' => '<b>Eve</b>', 'body' => '<script>alert(1)</script>', 'subject' => 'Hi & bye']);

    $html = (new NewMessageMail($message))->render();

    expect($html)
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('<b>Eve</b>')
        ->toContain('&lt;script&gt;')
        ->toContain(route('admin.messages.show', $message))
        ->and((new NewMessageMail($message))->envelope()->subject)->toContain('New contact message from <b>Eve</b>');
});

test('the message is saved even when the email can not be queued', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->postJson(route('messages.store'), messagePayload())->assertOk();

    expect(Message::query()->count())->toBe(1);
});

// ---------- The forms on the site ----------

test('the chat popup posts to the inbox and has no "Online" label', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('id="chat-form" method="POST" action="'.route('messages.store').'" data-message-form', false)
        ->assertSee('name="source" value="chat"', false)
        ->assertDontSee('- Online');
})->with(['/', '/about', '/contact', '/blog']);

test('the form text is escaped when it comes back', function () {
    $this->from(route('contact.index'))->followingRedirects()
        ->post(route('messages.store'), messagePayload(['name' => '"><script>alert(1)</script>', 'email' => 'bad']))
        ->assertDontSee('"><script>alert(1)</script>', false);
});
