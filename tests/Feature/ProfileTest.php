<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->get('/admin/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/admin/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'current_password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email can not be changed without the current password', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->from('/admin/profile')
        ->patch('/admin/profile', [
            'name' => $user->name,
            'email' => 'attacker@example.test',
        ])
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/admin/profile');

    $this->assertSame($user->email, $user->fresh()->email);
});

test('email can not be changed with a wrong current password', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch('/admin/profile', [
            'name' => $user->name,
            'email' => 'attacker@example.test',
            'current_password' => 'wrong-password',
        ])
        ->assertSessionHasErrors('current_password');

    $this->assertSame($user->email, $user->fresh()->email);
});

test('name can be changed without the current password', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->patch('/admin/profile', [
            'name' => 'New Name',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors();

    $this->assertSame('New Name', $user->fresh()->name);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/admin/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('an admin can delete their account when another admin exists', function () {
    User::factory()->admin()->create();
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/admin/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('the only admin account can not be deleted', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->from('/admin/profile')
        ->delete('/admin/profile', ['password' => 'password'])
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/admin/profile');

    $this->assertAuthenticatedAs($user);
    $this->assertNotNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->from('/admin/profile')
        ->delete('/admin/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/admin/profile');

    $this->assertNotNull($user->fresh());
});
