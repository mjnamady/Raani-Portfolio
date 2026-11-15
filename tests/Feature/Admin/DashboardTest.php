<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Comment;
use App\Models\Message;
use App\Models\MessageReply;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutVite();
    $this->admin = User::factory()->admin()->create(['name' => 'Ada Lovelace']);
});

test('guests are sent to the login page and non-admins are refused', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
});

test('the dashboard lives at /admin and is in the sidebar, while the old /dashboard URL stays gone', function () {
    expect(route('admin.dashboard', absolute: false))->toBe('/admin');

    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('href="'.route('admin.dashboard').'"', false);

    $this->get('/dashboard')->assertNotFound();
});

test('the cards show live counts, with drafts kept apart from published items', function () {
    Message::factory()->count(3)->create();
    Message::factory()->read()->create();
    Comment::factory()->count(2)->create();
    Project::factory()->published()->count(2)->create();
    Project::factory()->create();
    Post::factory()->published()->withThemeImages()->create();
    Post::factory()->withThemeImages()->count(2)->create();
    Service::factory()->count(2)->create();
    Service::factory()->inactive()->create();

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['3', 'Unread messages'])
        ->assertSeeInOrder(['2', 'Pending comments'])
        ->assertSeeInOrder(['2', 'Published projects', '+1 draft'])
        ->assertSeeInOrder(['1', 'Published posts', '+2 drafts'])
        ->assertSeeInOrder(['2', 'Services'])
        ->assertSee('db-card is-alert', false);
});

test('the latest 5 messages are listed newest first and open in the inbox', function () {
    foreach (range(1, 6) as $i) {
        Message::factory()->create(['name' => "Sender {$i}", 'created_at' => now()->subMinutes(10 - $i)]);
    }

    $newest = Message::query()->where('name', 'Sender 6')->first();

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSeeInOrder(['Sender 6', 'Sender 5', 'Sender 4', 'Sender 3', 'Sender 2'])
        ->assertDontSee('Sender 1')
        ->assertSee('href="'.route('admin.messages.show', $newest).'"', false)
        ->assertSee('Open inbox');

    expect(DashboardController::LATEST_MESSAGES)->toBe(5);
});

test('an empty inbox says so and email health is green', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSee('No messages yet.')
        ->assertSee('Emails are sending.')
        ->assertDontSee('waiting to be sent');
});

test('waiting emails and failed replies are flagged', function () {
    config(['queue.default' => 'database']);
    DB::table('jobs')->insert(['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null, 'available_at' => now()->subMinutes(5)->getTimestamp(), 'created_at' => now()->subMinutes(5)->getTimestamp()]);
    $failed = MessageReply::factory()->failed()->create();

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertSee('1 email is waiting to be sent.')
        ->assertSee('1 reply could not be sent.')
        ->assertSee('href="'.route('admin.messages.show', $failed->message_id).'"', false)
        ->assertDontSee('Emails are sending.');
});

test('signing in lands on the dashboard', function () {
    $this->post(route('login'), ['login' => $this->admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard', absolute: false));
});

test('message text is escaped on the dashboard', function () {
    Message::factory()->create(['name' => '<script>alert(1)</script>', 'subject' => '<b>x</b>']);

    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<b>x</b>', false);
});
