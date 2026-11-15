<?php

use App\Models\User;

dataset('admin pages', ['/admin/dashboard', '/admin/profile', '/admin/profile/settings']);

test('guests are redirected to the login page', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('admin pages');

test('non-admin users are forbidden', function (string $url) {
    $user = User::factory()->create();

    $this->actingAs($user)->get($url)->assertForbidden();
})->with('admin pages');

test('the admin can reach admin pages', function (string $url) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get($url)->assertOk();
})->with('admin pages');

test('non-admin users can not update the admin profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/admin/profile', ['name' => 'Hacker', 'email' => $user->email])
        ->assertForbidden();

    $this->assertNotSame('Hacker', $user->fresh()->name);
});

test('the old dashboard and profile URLs no longer exist', function (string $url) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get($url)->assertNotFound();
})->with(['/dashboard', '/profile']);

test('is_admin can not be mass assigned', function () {
    $user = User::create([
        'name' => 'Visitor',
        'email' => 'visitor@example.test',
        'password' => 'password',
        'is_admin' => true,
    ]);

    expect($user->fresh()->is_admin)->toBeFalse();
});

test('the public home page does not link to the login page', function () {
    $this->get('/')->assertOk()->assertDontSee(route('login'));
});
