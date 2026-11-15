<?php

use App\Models\User;
use Database\Seeders\BannerSectionSeeder;
use Database\Seeders\CounterAreaSeeder;
use Database\Seeders\LatestServicesSeeder;
use Database\Seeders\ServiceSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->seed([BannerSectionSeeder::class, ServiceSeeder::class, CounterAreaSeeder::class, LatestServicesSeeder::class]);
});

test('every admin editor tracks unsaved changes with a save bar', function (string $route, int $forms) {
    $html = $this->actingAs($this->admin)
        ->get(route($route))
        ->assertOk()
        ->assertSee(asset('backend/custom/js/admin-form-tracker.js'))
        ->assertSee(asset('backend/custom/css/admin-form-tracker.css'))
        ->getContent();

    expect(substr_count($html, 'data-track-changes'))->toBe($forms)
        ->and(substr_count($html, 'data-save-bar'))->toBe($forms);
})->with([
    'banner' => ['admin.banner', 1],
    'services area' => ['admin.services', 1],
    'counter area' => ['admin.counter', 1],
    'latest service area' => ['admin.latest-services', 1],
    'profile settings (profile + password)' => ['admin.profile.settings', 2],
]);

test('current password fields never count as unsaved changes', function () {
    $html = $this->actingAs($this->admin)->get(route('admin.profile.settings'))->getContent();

    expect(substr_count($html, 'autocomplete="current-password" data-track-ignore'))->toBe(2);
});

test('a failed save opens the banner tab that holds the error', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.banner'))
        ->followingRedirects()
        ->patch(route('admin.banner.update'), [
            'greeting' => 'Hi, I am', 'name' => 'Ada', 'role' => 'an Engineer',
            'biography_heading' => 'Bio', 'biography' => 'Text',
            'contact_heading' => '',
            'image_alt' => 'Portrait',
        ])
        ->assertOk()
        ->assertSee('class="tab-pane fade show active" id="bn-pane-contact"', false)
        ->assertSee('bn-tab active has-error', false);
});
