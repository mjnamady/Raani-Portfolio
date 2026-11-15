<?php

use App\Models\User;
use Database\Seeders\AdminDataSeeder;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate with their email', function () {
    $user = User::factory()->admin()->create();

    $response = $this->post('/login', [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can authenticate with their username', function () {
    $user = User::factory()->admin()->create(['username' => 'owner']);

    $response = $this->post('/login', [
        'login' => 'owner',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->admin()->create();

    $this->post('/login', [
        'login' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('users can not authenticate with an unknown username', function () {
    User::factory()->admin()->create(['username' => 'owner']);

    $this->post('/login', [
        'login' => 'someone-else',
        'password' => 'password',
    ])->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('login requires the login and password fields', function () {
    $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);

    $this->assertGuest();
});

test('login is throttled after five failed attempts', function () {
    User::factory()->admin()->create(['username' => 'owner']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['login' => 'owner', 'password' => 'wrong-password']);
    }

    $this->post('/login', ['login' => 'owner', 'password' => 'password'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('non-admin users can not log in even with valid credentials', function () {
    $user = User::factory()->create(['username' => 'visitor']);

    $this->post('/login', ['login' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('login');
    $this->post('/login', ['login' => 'visitor', 'password' => 'password'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('email and username of the same account share one throttle bucket', function () {
    $user = User::factory()->admin()->create(['username' => 'owner']);

    foreach (range(1, 3) as $attempt) {
        $this->post('/login', ['login' => 'owner', 'password' => 'wrong-password']);
    }

    foreach (range(1, 2) as $attempt) {
        $this->post('/login', ['login' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['login' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('login');

    $this->assertGuest();
});

test('seeded admin can log in with email or username', function () {
    config([
        'admin.username' => 'site-owner',
        'admin.email' => 'owner@example.test',
        'admin.password' => 'test-only-secret',
    ]);

    $this->seed(AdminDataSeeder::class);

    $this->post('/login', ['login' => 'site-owner', 'password' => 'test-only-secret']);
    $this->assertAuthenticated();

    $this->post('/logout');
    $this->assertGuest();

    $this->post('/login', ['login' => 'owner@example.test', 'password' => 'test-only-secret']);
    $this->assertAuthenticated();
});

test('users can logout', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
