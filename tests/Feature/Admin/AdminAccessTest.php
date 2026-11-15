<?php

use App\Models\User;

dataset('admin pages', ['/admin/banner', '/admin/profile', '/admin/profile/settings']);

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

test('the sidebar banner link navigates instead of toggling a collapse', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)->get(route('admin.profile.settings'))->assertOk()->getContent();

    preg_match('#<a href="'.preg_quote(route('admin.banner'), '#').'"[^>]*>\s*<div class="">.*?<span>Banner Section</span>#s', $html, $match);

    expect($match)->not->toBeEmpty()
        ->and($match[0])->not->toContain('data-bs-toggle');
});

test('admin pages render in dark mode by default and apply the saved theme before anything else', function (string $url) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get($url)
        ->assertOk()
        ->assertSee('<body class="dark layout-boxed">', false)
        ->assertSeeInOrder([
            "localStorage.getItem('adminTheme') !== 'light'",
            'id="load_screen"',
            asset('backend/layouts/modern-dark-menu/app.js'),
            asset('backend/custom/js/admin-theme.js'),
        ], false);
})->with(['/admin/banner', '/admin/profile/settings']);

test('the login page defaults to dark mode and shares the admin theme preference', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('<body class="auth-page dark">', false)
        ->assertSee("localStorage.getItem('adminTheme') !== 'light'", false);
});

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
