<?php

use App\Http\Controllers\Admin\MessageController;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

// ---------- Access ----------

test('guests and non-admins can not read or manage messages', function () {
    $message = Message::factory()->create();
    $user = User::factory()->create();

    $this->get(route('admin.messages.index'))->assertRedirect(route('login'));
    $this->get(route('admin.messages.show', $message))->assertRedirect(route('login'));

    $this->actingAs($user)->get(route('admin.messages.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.messages.show', $message))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.messages.replied', $message))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.messages.unread', $message))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.messages.destroy', $message))->assertForbidden();

    expect($message->fresh())->read_at->toBeNull()->replied_at->toBeNull()
        ->and($user->can('view', $message))->toBeFalse()
        ->and($this->admin->can('delete', $message))->toBeTrue();
});

// ---------- The list ----------

test('the inbox lists messages newest first with unread, source and replied markers', function () {
    Message::factory()->read()->create(['name' => 'Older Person', 'created_at' => now()->subDay()]);
    Message::factory()->chat()->create(['name' => 'Newer Person', 'body' => 'Quick question about your rates.']);
    Message::factory()->replied()->create(['name' => 'Answered Person', 'created_at' => now()->subDays(2)]);

    $this->actingAs($this->admin)->get(route('admin.messages.index'))
        ->assertOk()
        ->assertSeeInOrder(['Newer Person', 'Older Person', 'Answered Person'])
        ->assertSee('Quick question about your rates.')
        ->assertSee('mx-chip mx-chip-chat', false)
        ->assertSee('mx-row is-unread', false)
        ->assertSee('Replied')
        ->assertSee('Select a message to read it.');
});

test('the inbox can be filtered by status and source, with counts per tab', function () {
    Message::factory()->create(['name' => 'Unread Contact']);
    Message::factory()->chat()->read()->create(['name' => 'Read Chat']);
    Message::factory()->replied()->create(['name' => 'Replied Contact']);

    $this->actingAs($this->admin)->get(route('admin.messages.index', ['status' => 'unread']))
        ->assertSee('Unread Contact')->assertDontSee('Read Chat')->assertDontSee('Replied Contact');

    $this->actingAs($this->admin)->get(route('admin.messages.index', ['status' => 'replied']))
        ->assertSee('Replied Contact')->assertDontSee('Unread Contact');

    $this->actingAs($this->admin)->get(route('admin.messages.index', ['source' => 'chat']))
        ->assertSee('Read Chat')->assertDontSee('Unread Contact');

    $this->actingAs($this->admin)->get(route('admin.messages.index', ['status' => ['x'], 'source' => 'nope']))
        ->assertOk()->assertSee('Unread Contact')->assertSee('Read Chat');

    $this->actingAs($this->admin)->get(route('admin.messages.index'))
        ->assertSee('All <span>3</span>', false)
        ->assertSee('Unread <span>1</span>', false)
        ->assertSee('Replied <span>1</span>', false);
});

test('the inbox is paginated and an empty inbox says so', function () {
    $this->actingAs($this->admin)->get(route('admin.messages.index'))->assertSee('No messages yet');
    $this->actingAs($this->admin)->get(route('admin.messages.index', ['status' => 'unread']))->assertSee('All caught up');

    Message::factory()->count(MessageController::PER_PAGE + 1)->create();

    $this->actingAs($this->admin)->get(route('admin.messages.index'))->assertSee('page=2', false);
});

test('the sidebar shows the unread count', function () {
    Message::factory()->count(2)->create();
    Message::factory()->read()->create();

    $this->actingAs($this->admin)->get(route('admin.banner'))
        ->assertSee('title="2 unread messages"', false)
        ->assertSee('href="'.route('admin.messages.index').'"', false);
});

// ---------- Reading ----------

test('opening a message shows it in full and marks it as read', function () {
    $project = Project::factory()->published()->create(['title' => 'Harbour App']);
    $message = Message::factory()->create([
        'source' => 'project', 'project_id' => $project->id,
        'name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'phone' => '+44 20 7946 0000',
        'subject' => 'About: Harbour App', 'body' => "Line one\nLine two",
    ]);

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('<a href="mailto:ada@example.test">ada@example.test</a>', false)
        ->assertSee('<a href="tel:+442079460000">', false)
        ->assertSee("Line one\nLine two")
        ->assertSee('href="'.route('admin.projects.edit', $project).'"', false)
        ->assertSee('href="mailto:ada@example.test?subject=Re%3A%20About%3A%20Harbour%20App"', false);

    expect($message->fresh()->read_at)->not->toBeNull();
});

test('a message about a deleted project still opens', function () {
    $project = Project::factory()->published()->create();
    $message = Message::factory()->create(['source' => 'project', 'project_id' => $project->id]);

    $project->delete();

    expect($message->fresh()->project_id)->toBeNull();
    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))->assertOk()->assertSee('Deleted project');
});

test('the reply link is encoded, so a stored address can not add mail parameters', function () {
    $message = Message::factory()->create(['email' => 'x?bcc=spy@example.com', 'subject' => 'Hi&bye']);

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))
        ->assertSee('href="mailto:x%3Fbcc%3Dspy@example.com?subject=Re%3A%20Hi%26bye"', false);
});

// ---------- Actions ----------

test('the admin can mark messages replied, not replied and unread, keeping the filters', function () {
    $message = Message::factory()->read()->create();

    $this->actingAs($this->admin)->patch(route('admin.messages.replied', $message), ['status' => 'unread', 'source' => 'contact'])
        ->assertRedirect(route('admin.messages.show', ['message' => $message, 'status' => 'unread', 'source' => 'contact']))
        ->assertSessionHas('status', 'Marked as replied.');
    expect($message->fresh()->replied_at)->not->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.messages.unreplied', $message));
    expect($message->fresh()->replied_at)->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.messages.unread', $message))
        ->assertRedirect(route('admin.messages.index'));
    expect($message->fresh()->read_at)->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.messages.read', $message));
    expect($message->fresh()->read_at)->not->toBeNull();
});

test('the admin can delete a message after a confirmation', function () {
    $message = Message::factory()->create(['name' => 'Ada']);

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))
        ->assertSee('data-confirm="Delete the message from Ada? This can not be undone."', false);

    $this->actingAs($this->admin)->delete(route('admin.messages.destroy', $message))
        ->assertRedirect(route('admin.messages.index'))
        ->assertSessionHas('status', 'The message has been deleted.');

    expect(Message::query()->count())->toBe(0);
});

test('unknown messages are 404', function () {
    $this->actingAs($this->admin)->get(route('admin.messages.show', 999))->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.messages.destroy', 999))->assertNotFound();
});

test('message text is escaped in the inbox', function () {
    $message = Message::factory()->create(['name' => '<script>alert(1)</script>', 'body' => '<img src=x onerror=alert(2)>', 'subject' => '<b>x</b>']);

    $this->actingAs($this->admin)->get(route('admin.messages.show', $message))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<b>x</b>', false);
});

// ---------- Privacy ----------

test('visitor IP and browser details are cleared from messages older than 90 days only', function () {
    $old = Message::factory()->create(['created_at' => now()->subDays(Message::KEEP_VISITOR_DATA_DAYS + 1)]);
    $recent = Message::factory()->create(['created_at' => now()->subDays(10)]);

    $this->artisan('messages:forget-visitor-data')->expectsOutput('Cleared visitor details from 1 message.')->assertSuccessful();

    expect($old->fresh())->ip_address->toBeNull()->user_agent->toBeNull()->body->not->toBeNull()
        ->and($recent->fresh()->ip_address)->not->toBeNull();
});

test('the visitor details clean-up is scheduled daily', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('messages:forget-visitor-data')->assertSuccessful();
});
