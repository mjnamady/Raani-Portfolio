<?php

use App\Models\User;
use Database\Seeders\AdminDataSeeder;
use Illuminate\Support\Facades\Hash;

test('seeder creates the admin account from config', function () {
    config([
        'admin.username' => 'site-owner',
        'admin.email' => 'owner@example.test',
        'admin.password' => 'test-only-secret',
    ]);

    $this->seed(AdminDataSeeder::class);

    $admin = User::where('email', 'owner@example.test')->firstOrFail();

    expect($admin->username)->toBe('site-owner')
        ->and($admin->is_admin)->toBeTrue()
        ->and(Hash::check('test-only-secret', $admin->password))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

test('seeder stores the configured email in lower case', function () {
    config(['admin.email' => '  Owner@Example.TEST ', 'admin.password' => 'test-only-secret']);

    $this->seed(AdminDataSeeder::class);

    expect(User::where('email', 'owner@example.test')->exists())->toBeTrue();
});

test('changing the configured email does not create a second admin', function () {
    config(['admin.email' => 'owner@example.test', 'admin.password' => 'test-only-secret']);
    $this->seed(AdminDataSeeder::class);

    config(['admin.email' => 'new-owner@example.test']);
    $this->seed(AdminDataSeeder::class);

    expect(User::count())->toBe(1)
        ->and(User::where('email', 'new-owner@example.test')->exists())->toBeFalse();
});

test('seeder marks an existing account with the configured email as admin without changing its password', function () {
    $user = User::factory()->create(['email' => 'owner@example.test']);
    config(['admin.email' => 'owner@example.test', 'admin.password' => 'test-only-secret']);

    $this->seed(AdminDataSeeder::class);

    $user->refresh();

    expect($user->is_admin)->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeTrue()
        ->and(User::count())->toBe(1);
});

test('seeder refuses a weak password in production', function () {
    config(['admin.email' => 'owner@example.test', 'admin.password' => 'weakpass']);
    app()->detectEnvironment(fn () => 'production');

    app(AdminDataSeeder::class)->run();

    expect(User::count())->toBe(0);
});

test('seeder accepts a strong password in production', function () {
    config(['admin.email' => 'owner@example.test', 'admin.password' => 'Str0ng!Owner#Pass']);
    app()->detectEnvironment(fn () => 'production');

    app(AdminDataSeeder::class)->run();

    expect(User::where('email', 'owner@example.test')->value('is_admin'))->toBeTrue();
});

test('reseeding does not reset an existing admin password', function () {
    config([
        'admin.email' => 'owner@example.test',
        'admin.password' => 'test-only-secret',
    ]);

    $this->seed(AdminDataSeeder::class);

    $admin = User::where('email', 'owner@example.test')->firstOrFail();
    $admin->update(['password' => 'changed-by-owner']);

    $this->seed(AdminDataSeeder::class);

    expect(User::count())->toBe(1)
        ->and(Hash::check('changed-by-owner', $admin->fresh()->password))->toBeTrue();
});

test('seeder generates a random password when none is configured locally', function () {
    config(['admin.email' => 'owner@example.test', 'admin.password' => null]);

    $this->seed(AdminDataSeeder::class);

    $admin = User::where('email', 'owner@example.test')->firstOrFail();

    expect(Hash::check('Admin@12345', $admin->password))->toBeFalse()
        ->and(Hash::check('password', $admin->password))->toBeFalse();
});

test('seeder creates no account in production without configured credentials', function () {
    config(['admin.email' => null, 'admin.password' => null]);
    app()->detectEnvironment(fn () => 'production');

    app(AdminDataSeeder::class)->run();

    expect(User::count())->toBe(0);
});
