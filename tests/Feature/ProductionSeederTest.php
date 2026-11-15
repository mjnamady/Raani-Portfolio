<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['admin.email' => 'owner@example.test', 'admin.username' => 'owner', 'admin.password' => 'Str0ng!Passw0rd#2026']);
});

test('the production seed creates the admin and the site settings, but no demo content', function () {
    $this->seed(ProductionSeeder::class);

    expect(User::query()->where('is_admin', true)->sole())->email->toBe('owner@example.test')
        ->and(Setting::get('site.name'))->toBe(config('app.name'))
        ->and(Project::query()->count())->toBe(0)
        ->and(Post::query()->count())->toBe(0)
        ->and(Comment::query()->count())->toBe(0)
        ->and(Setting::get('contact.emails'))->toBeNull();
});

test('running it on every deploy changes nothing that already exists', function () {
    $this->seed(ProductionSeeder::class);
    Setting::put('site.name', 'My Portfolio');
    config(['admin.password' => 'An0ther!Passw0rd#2026']);

    $this->seed(ProductionSeeder::class);

    expect(User::query()->count())->toBe(1)
        ->and(Hash::check('Str0ng!Passw0rd#2026', User::query()->sole()->password))->toBeTrue()
        ->and(Setting::get('site.name'))->toBe('My Portfolio');
});
