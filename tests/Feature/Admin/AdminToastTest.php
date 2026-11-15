<?php

use App\Models\User;
use Database\Seeders\BannerSectionSeeder;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->seed(BannerSectionSeeder::class);
});

test('admin pages load the toast assets and an empty toast container', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.banner'))
        ->assertOk()
        ->assertSee(asset('backend/custom/css/admin-toasts.css'))
        ->assertSee(asset('backend/custom/js/admin-toasts.js'))
        ->assertSee('id="adminToastContainer"', false)
        ->assertDontSee('admin-toast-success', false)
        ->assertDontSee('admin-toast-danger', false);
});

test('a successful save shows a success toast with the status message', function () {
    $this->actingAs($this->admin)
        ->followingRedirects()
        ->patch(route('admin.banner.update'), [
            'greeting' => 'Hello, I am',
            'name' => 'Ada Lovelace',
            'role' => 'a Software Engineer',
            'biography_heading' => 'About me :',
            'biography' => 'I build fast, accessible web apps.',
            'contact_heading' => 'Reach me :',
            'address' => '12 Analytical Street, London',
            'email' => 'ada@example.test',
            'phone' => '+44 (0) 20 7946 0000',
            'image_alt' => 'Portrait of Ada Lovelace',
            'is_active' => '1',
        ])
        ->assertOk()
        ->assertSee('admin-toast-success', false)
        ->assertSee('role="status"', false)
        ->assertSee('The banner section has been updated.');
});

test('a failed validation shows an error toast', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.banner'))
        ->followingRedirects()
        ->patch(route('admin.banner.update'), ['name' => ''])
        ->assertOk()
        ->assertSee('admin-toast-danger', false)
        ->assertSee('role="alert"', false)
        ->assertSee('Please correct the highlighted fields and save again.')
        ->assertDontSee('admin-toast-success', false);
});

test('errors in a named error bag also show an error toast', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.profile.settings'))
        ->followingRedirects()
        ->put(route('admin.profile.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'N3w-Passw0rd!xyz',
            'password_confirmation' => 'N3w-Passw0rd!xyz',
        ])
        ->assertOk()
        ->assertSee('admin-toast-danger', false);
});

test('flash messages in toasts are escaped', function () {
    $this->actingAs($this->admin)
        ->withSession(['status' => '<script>alert(1)</script>'])
        ->get(route('admin.banner'))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});
