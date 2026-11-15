<?php

use App\Jobs\SendMessageReply;
use App\Mail\MessageReplyMail;
use App\Mail\NewMessageMail;
use App\Models\Message;
use App\Models\MessageReply;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['email' => 'owner@example.test']);
    $this->message = Message::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'subject' => 'A new website', 'body' => "Hello,\nCan we talk?"]);
});

// ---------- Access ----------

test('guests and non-admins can not reply or retry', function () {
    Queue::fake();
    $reply = MessageReply::factory()->failed()->create(['message_id' => $this->message->id]);
    $user = User::factory()->create();

    $this->post(route('admin.messages.replies.store', $this->message), ['subject' => 'Re', 'body' => 'Hi'])->assertRedirect(route('login'));
    $this->actingAs($user)->post(route('admin.messages.replies.store', $this->message), ['subject' => 'Re', 'body' => 'Hi'])->assertForbidden();
    $this->actingAs($user)->patch(route('admin.messages.replies.retry', [$this->message, $reply]))->assertForbidden();

    expect(MessageReply::query()->count())->toBe(1);
    Queue::assertNothingPushed();
});

// ---------- Sending ----------

test('a reply is saved, queued and marks the message as replied', function () {
    Queue::fake();

    $this->actingAs($this->admin)
        ->post(route('admin.messages.replies.store', $this->message), ['subject' => '  Re: A new website ', 'body' => "Hi Ada,\r\nYes, gladly."])
        ->assertRedirect(route('admin.messages.show', $this->message))
        ->assertSessionHas('status', 'Your reply to Ada Lovelace is being sent.');

    $reply = MessageReply::query()->sole();

    expect($reply)
        ->subject->toBe('Re: A new website')
        ->body->toBe("Hi Ada,\nYes, gladly.")
        ->user_id->toBe($this->admin->id)
        ->status()->toBe('sending')
        ->and($this->message->fresh())->replied_at->not->toBeNull()->read_at->not->toBeNull();

    Queue::assertPushed(SendMessageReply::class, fn (SendMessageReply $job) => $job->reply->is($reply));
});

test('the reply always goes to the message\'s own address, whatever the form sends', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post(route('admin.messages.replies.store', $this->message), [
        'subject' => 'Re: hi', 'body' => 'Hello there', 'email' => 'someone-else@example.test', 'to' => 'spam@example.test', 'message_id' => 999,
    ])->assertSessionHasNoErrors();

    Mail::assertSent(MessageReplyMail::class, fn (MessageReplyMail $mail) => $mail->hasTo('ada@example.test')
        && ! $mail->hasTo('someone-else@example.test')
        && ! $mail->hasTo('spam@example.test'));

    expect(MessageReply::query()->sole())->message_id->toBe($this->message->id)->status()->toBe('sent');
});

test('the reply email comes from the site, replies to the owner and quotes the original message', function () {
    Setting::put('site.name', 'Ada Studio');
    $reply = MessageReply::factory()->create(['message_id' => $this->message->id, 'user_id' => $this->admin->id, 'subject' => 'Re: A new website', 'body' => "Yes!\nLet us talk."]);

    $mail = new MessageReplyMail($reply);
    $html = $mail->render();

    expect($mail->envelope())
        ->subject->toBe('Re: A new website')
        ->and($mail->envelope()->from->address)->toBe(config('mail.from.address'))
        ->and($mail->envelope()->from->name)->toBe('Ada Studio')
        ->and($mail->envelope()->replyTo[0]->address)->toBe('owner@example.test')
        ->and($html)->toContain("Yes!\nLet us talk.")
        ->toContain('Ada Lovelace wrote:')
        ->toContain("Hello,\nCan we talk?");
});

test('reply text and the quoted message are escaped in the email and the thread', function () {
    $this->message->update(['body' => '<script>alert(1)</script> visitor']);
    $reply = MessageReply::factory()->sent()->create(['message_id' => $this->message->id, 'subject' => '<b>Re</b>', 'body' => '<img src=x onerror=alert(2)>']);

    expect((new MessageReplyMail($reply))->render())
        ->not->toContain('<script>alert(1)</script>')
        ->not->toContain('<img src=x onerror=alert(2)>');

    $this->actingAs($this->admin)->get(route('admin.messages.show', $this->message))
        ->assertOk()
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<b>Re</b>', false);
});

// ---------- Validation ----------

test('replies are validated', function (array $payload, string $field) {
    Queue::fake();

    $this->actingAs($this->admin)
        ->post(route('admin.messages.replies.store', $this->message), $payload + ['subject' => 'Re: hi', 'body' => 'Hello there'])
        ->assertSessionHasErrorsIn('reply', $field);

    expect(MessageReply::query()->count())->toBe(0)
        ->and($this->message->fresh()->replied_at)->toBeNull();
})->with([
    'missing text' => [['body' => ''], 'body'],
    'too short' => [['body' => 'x'], 'body'],
    'too long' => [['body' => str_repeat('x', MessageReply::MAX_BODY_LENGTH + 1)], 'body'],
    'missing subject' => [['subject' => ''], 'subject'],
    'long subject' => [['subject' => str_repeat('s', 151)], 'subject'],
    'invalid utf-8' => [['body' => "Hello \xFF there"], 'body'],
    'array text' => [['body' => ['x']], 'body'],
]);

test('line breaks in the subject are removed, so no mail header can be added', function () {
    Queue::fake();

    $this->actingAs($this->admin)->post(route('admin.messages.replies.store', $this->message), [
        'subject' => "Re: hi\r\nBcc: spy@example.test", 'body' => 'Hello there',
    ])->assertSessionHasNoErrors();

    expect(MessageReply::query()->sole()->subject)->toBe('Re: hi Bcc: spy@example.test');
});

test('the reply box shows its errors and keeps the draft', function () {
    $this->actingAs($this->admin)->from(route('admin.messages.show', $this->message))->followingRedirects()
        ->post(route('admin.messages.replies.store', $this->message), ['subject' => 'Re: hi', 'body' => 'x'])
        ->assertOk()
        ->assertSee('The reply field must be at least 2 characters.');
});

test('at most 30 replies an hour are sent', function () {
    Queue::fake();

    foreach (range(1, 30) as $i) {
        $this->actingAs($this->admin)->post(route('admin.messages.replies.store', $this->message), ['subject' => 'Re', 'body' => "Reply {$i}"]);
    }

    $this->actingAs($this->admin)->post(route('admin.messages.replies.store', $this->message), ['subject' => 'Re', 'body' => 'One more'])
        ->assertRedirect(route('admin.messages.show', $this->message))
        ->assertSessionHasErrorsIn('reply', 'body');

    expect(MessageReply::query()->count())->toBe(30);
});

// ---------- Delivery ----------

test('the job marks the reply sent, and a reply that was sent is never sent twice', function () {
    Mail::fake();
    $reply = MessageReply::factory()->create(['message_id' => $this->message->id]);

    (new SendMessageReply($reply))->handle();
    (new SendMessageReply($reply->fresh()))->handle();

    Mail::assertSentCount(1);
    expect($reply->fresh()->status())->toBe('sent');
});

test('replies and owner notifications are retried with growing pauses', function () {
    $reply = MessageReply::factory()->create(['message_id' => $this->message->id]);

    expect(new SendMessageReply($reply))->tries->toBe(5)->backoff()->toBe([15, 60, 300, 900])
        ->and(new NewMessageMail($this->message))->tries->toBe(5)->backoff()->toBe([15, 60, 300, 900]);
});

test('when every try fails the reply shows as failed with a reason and can be retried', function () {
    Queue::fake();
    $reply = MessageReply::factory()->create(['message_id' => $this->message->id]);

    (new SendMessageReply($reply))->failed(new RuntimeException("550 Too many emails\nper second"));

    expect($reply->fresh())->status()->toBe('failed')->error->toBe('RuntimeException: 550 Too many emails per second');

    $this->actingAs($this->admin)->get(route('admin.messages.show', $this->message))
        ->assertSee('RuntimeException: 550 Too many emails per second')
        ->assertSee('action="'.route('admin.messages.replies.retry', [$this->message, $reply]).'"', false);

    $this->actingAs($this->admin)->patch(route('admin.messages.replies.retry', [$this->message, $reply]))
        ->assertSessionHas('status', 'Trying to send your reply again.');

    expect($reply->fresh())->status()->toBe('sending')->error->toBeNull();
    Queue::assertPushed(SendMessageReply::class);
});

test('only failed replies of the same message can be retried', function () {
    Queue::fake();
    $sent = MessageReply::factory()->sent()->create(['message_id' => $this->message->id]);
    $otherMessagesReply = MessageReply::factory()->failed()->create();

    $this->actingAs($this->admin)->patch(route('admin.messages.replies.retry', [$this->message, $sent]))->assertNotFound();
    $this->actingAs($this->admin)->patch(route('admin.messages.replies.retry', [$this->message, $otherMessagesReply]))->assertNotFound();

    Queue::assertNothingPushed();
});

test('the thread lists replies oldest first with their status', function () {
    MessageReply::factory()->sent()->create(['message_id' => $this->message->id, 'body' => 'First answer', 'created_at' => now()->subHour()]);
    MessageReply::factory()->create(['message_id' => $this->message->id, 'body' => 'Second answer']);

    $this->actingAs($this->admin)->get(route('admin.messages.show', $this->message))
        ->assertSeeInOrder(['First answer', 'Second answer'])
        ->assertSee('mx-reply is-sent', false)
        ->assertSee('Sending…')
        ->assertSee('To</span>', false)
        ->assertSee('value="Re: A new website"', false);
});

test('deleting a message deletes its replies', function () {
    MessageReply::factory()->sent()->create(['message_id' => $this->message->id]);

    $this->actingAs($this->admin)->delete(route('admin.messages.destroy', $this->message));

    expect(MessageReply::query()->count())->toBe(0);
});

// ---------- Waiting emails warning ----------

test('the inbox warns when emails have been waiting for the queue worker', function () {
    $this->actingAs($this->admin)->get(route('admin.messages.index'))->assertDontSee('waiting to be sent');

    config(['queue.default' => 'database']);
    $job = ['queue' => 'default', 'payload' => '{}', 'attempts' => 0, 'reserved_at' => null];
    DB::table('jobs')->insert($job + ['available_at' => now()->subMinutes(5)->getTimestamp(), 'created_at' => now()->subMinutes(5)->getTimestamp()]);
    // Waiting for a retry pause: not due yet, so not counted.
    DB::table('jobs')->insert($job + ['available_at' => now()->addMinutes(5)->getTimestamp(), 'created_at' => now()->subMinutes(5)->getTimestamp()]);

    $this->actingAs($this->admin)->get(route('admin.messages.index'))
        ->assertSee('1 email is waiting to be sent.')
        ->assertSee('php artisan queue:work');
});
