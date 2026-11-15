<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('unknown and known emails get the same reset response', function () {
    Notification::fake();

    $user = User::factory()->create();

    $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
    $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.test']);

    $known->assertSessionHasNoErrors();
    $unknown->assertSessionHasNoErrors();
    expect($unknown->getSession()->get('status'))->toBe($known->getSession()->get('status'));
});

test('reset form gives the same error for unknown emails and bad tokens', function () {
    $user = User::factory()->create();

    $payload = fn (string $email) => [
        'token' => 'invalid-token',
        'email' => $email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ];

    $message = 'This password reset link is invalid or has expired.';

    $this->post('/reset-password', $payload($user->email))
        ->assertSessionHasErrors(['email' => $message]);

    $this->post('/reset-password', $payload('nobody@example.test'))
        ->assertSessionHasErrors(['email' => $message]);
});

test('reset link requests for one email are limited across IP addresses', function () {
    Notification::fake();

    foreach (range(1, 10) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$attempt}"])
            ->post('/forgot-password', ['email' => 'owner@example.test'])
            ->assertRedirect();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
        ->post('/forgot-password', ['email' => 'owner@example.test'])
        ->assertTooManyRequests();
});

test('reset link requests are rate limited', function () {
    Notification::fake();

    foreach (range(1, 5) as $attempt) {
        $this->post('/forgot-password', ['email' => "guess{$attempt}@example.test"])->assertRedirect();
    }

    $this->post('/forgot-password', ['email' => 'guess6@example.test'])->assertTooManyRequests();
});

test('password reset submissions are rate limited', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'owner@example.test',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
    }

    $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'owner@example.test',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertTooManyRequests();
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
