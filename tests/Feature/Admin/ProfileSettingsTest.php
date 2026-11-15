<?php

use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Owner',
        'username' => 'ada',
        'email' => 'ada@example.test',
    ]);
});

function profilePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ada Owner',
        'username' => 'ada',
        'email' => 'ada@example.test',
    ], $overrides);
}

// ---------- Access ----------

test('guests are redirected from profile settings', function () {
    auth()->logout();

    $this->get(route('admin.profile.settings'))->assertRedirect(route('login'));
    $this->patch(route('admin.profile.settings.update'), profilePayload())->assertRedirect(route('login'));
    $this->put(route('admin.profile.password.update'))->assertRedirect(route('login'));
});

test('non-admin users can not view or update profile settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.profile.settings'))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.profile.settings.update'), profilePayload())->assertForbidden();
    $this->actingAs($user)->put(route('admin.profile.password.update'), [
        'current_password' => 'password',
        'password' => 'N3w!Password',
        'password_confirmation' => 'N3w!Password',
    ])->assertForbidden();
});

// ---------- Profile tab ----------

test('profile settings page shows the admin details from the database', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.profile.settings'))
        ->assertOk()
        ->assertSee('Ada Owner')
        ->assertSee('value="ada"', false)
        ->assertSee('ada@example.test')
        ->assertSee(asset(User::DEFAULT_PROFILE_IMAGE))
        ->assertDontSee('shaun@mail.com');
});

test('admin can update name and username without the current password', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['name' => 'Ada Lovelace', 'username' => 'ada_l']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.settings'))
        ->assertSessionHas('status');

    $this->admin->refresh();

    expect($this->admin->name)->toBe('Ada Lovelace')
        ->and($this->admin->username)->toBe('ada_l');
});

test('changing the email requires the correct current password', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['email' => 'new@example.test']))
        ->assertSessionHasErrors('current_password');

    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['email' => 'new@example.test', 'current_password' => 'wrong']))
        ->assertSessionHasErrors('current_password');

    expect($this->admin->fresh()->email)->toBe('ada@example.test');

    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['email' => 'new@example.test', 'current_password' => 'password']))
        ->assertSessionHasNoErrors();

    expect($this->admin->fresh()->email)->toBe('new@example.test');
});

test('profile fields are validated', function (array $overrides, string $field) {
    User::factory()->create(['username' => 'taken', 'email' => 'taken@example.test']);

    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'name missing' => [['name' => ''], 'name'],
    'name too long' => [['name' => str_repeat('a', 256)], 'name'],
    'username missing' => [['username' => ''], 'username'],
    'username too short' => [['username' => 'ab'], 'username'],
    'username with spaces' => [['username' => 'ada owner'], 'username'],
    'username shaped like an email' => [['username' => 'ada@example.test'], 'username'],
    'username taken' => [['username' => 'taken'], 'username'],
    'email invalid' => [['email' => 'not-an-email', 'current_password' => 'password'], 'email'],
    'email taken' => [['email' => 'taken@example.test', 'current_password' => 'password'], 'email'],
]);

test('is_admin can not be changed through the profile form', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['is_admin' => false]))
        ->assertSessionHasNoErrors();

    expect($this->admin->fresh()->is_admin)->toBeTrue();
});

// ---------- Profile image ----------

test('admin can upload a profile image which is resized, renamed and stored on the public disk', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload([
            'profile_image' => UploadedFile::fake()->image('my photo.jpg', 1200, 800),
        ]))
        ->assertSessionHasNoErrors();

    $path = $this->admin->fresh()->profile_image;

    expect($path)->toStartWith('profile-images/')
        ->and($path)->toEndWith('.webp')
        ->and($path)->not->toContain('my photo');

    Storage::disk('public')->assertExists($path);

    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect([$width, $height])->toBe([400, 400]);

    $this->actingAs($this->admin)
        ->get(route('admin.profile.settings'))
        ->assertSee(Storage::disk('public')->url($path));
});

test('uploading a new profile image deletes the old one', function () {
    $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload([
        'profile_image' => UploadedFile::fake()->image('first.png', 500, 500),
    ]));
    $oldPath = $this->admin->fresh()->profile_image;

    $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload([
        'profile_image' => UploadedFile::fake()->image('second.png', 500, 500),
    ]));
    $newPath = $this->admin->fresh()->profile_image;

    expect($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

test('saving without a new image keeps the existing one', function () {
    $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload([
        'profile_image' => UploadedFile::fake()->image('first.png', 500, 500),
    ]));
    $path = $this->admin->fresh()->profile_image;

    $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload(['name' => 'Renamed']));

    expect($this->admin->fresh()->profile_image)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('invalid profile images are rejected', function (UploadedFile $file) {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload(['profile_image' => $file]))
        ->assertSessionHasErrors('profile_image');

    expect($this->admin->fresh()->profile_image)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'not an image' => fn () => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    'php file renamed to jpg' => fn () => UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "hi";'),
    'svg' => fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
    'gif' => fn () => UploadedFile::fake()->image('anim.gif', 300, 300),
    'larger than 2 MB' => fn () => UploadedFile::fake()->image('big.jpg', 600, 600)->size(3000),
    'too small' => fn () => UploadedFile::fake()->image('tiny.jpg', 50, 50),
]);

// ---------- Change password tab ----------

test('admin can change their password', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.profile.password.update'), [
            'current_password' => 'password',
            'password' => 'N3w!Password',
            'password_confirmation' => 'N3w!Password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.settings'))
        ->assertSessionHas('active_tab', 'password');

    expect(Hash::check('N3w!Password', $this->admin->fresh()->password))->toBeTrue();
    $this->assertAuthenticatedAs($this->admin);
});

test('changing the password rotates the remember token', function () {
    $oldToken = $this->admin->remember_token;

    $this->actingAs($this->admin)->put(route('admin.profile.password.update'), [
        'current_password' => 'password',
        'password' => 'N3w!Password',
        'password_confirmation' => 'N3w!Password',
    ]);

    expect($this->admin->fresh()->remember_token)->not->toBe($oldToken);
});

test('password change is validated', function (array $payload, string $field) {
    $this->actingAs($this->admin)
        ->put(route('admin.profile.password.update'), $payload)
        ->assertSessionHasErrorsIn('updatePassword', $field);

    expect(Hash::check('password', $this->admin->fresh()->password))->toBeTrue();
})->with([
    'current password missing' => [['password' => 'N3w!Password', 'password_confirmation' => 'N3w!Password'], 'current_password'],
    'current password wrong' => [['current_password' => 'wrong', 'password' => 'N3w!Password', 'password_confirmation' => 'N3w!Password'], 'current_password'],
    'new password missing' => [['current_password' => 'password'], 'password'],
    'confirmation does not match' => [['current_password' => 'password', 'password' => 'N3w!Password', 'password_confirmation' => 'Other!Pass1'], 'password'],
    'too short' => [['current_password' => 'password', 'password' => 'N3w!pa', 'password_confirmation' => 'N3w!pa'], 'password'],
    'no upper case' => [['current_password' => 'password', 'password' => 'n3w!password', 'password_confirmation' => 'n3w!password'], 'password'],
    'no number' => [['current_password' => 'password', 'password' => 'New!Password', 'password_confirmation' => 'New!Password'], 'password'],
    'no symbol' => [['current_password' => 'password', 'password' => 'N3wPassword', 'password_confirmation' => 'N3wPassword'], 'password'],
    'same as current' => [['current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password'], 'password'],
]);

test('password errors reopen the change password tab', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.profile.settings'))
        ->put(route('admin.profile.password.update'), ['current_password' => 'wrong'])
        ->assertRedirect(route('admin.profile.settings'));

    $this->actingAs($this->admin)
        ->get(route('admin.profile.settings'))
        ->assertSee('id="nav-password" role="tabpanel"', false)
        ->assertSee('tab-pane fade show active" id="nav-password"', false);
});

// ---------- Security hardening ----------

test('the old Breeze password route no longer exists', function () {
    $this->actingAs($this->admin)
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'aaaaaaaa',
            'password_confirmation' => 'aaaaaaaa',
        ])
        ->assertNotFound();

    expect(Hash::check('password', $this->admin->fresh()->password))->toBeTrue();
});

test('the Breeze profile page posts password changes to the hardened admin route', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.profile.edit'))
        ->assertOk()
        ->assertSee('action="'.route('admin.profile.password.update').'"', false);
});

test('email change password guesses are rate limited', function () {
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload([
            'email' => 'attacker@example.test',
            'current_password' => "guess-{$attempt}",
        ]))->assertSessionHasErrors('current_password');
    }

    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload([
            'email' => 'attacker@example.test',
            'current_password' => 'password',
        ]))
        ->assertTooManyRequests();

    expect($this->admin->fresh()->email)->toBe('ada@example.test');
});

test('the Breeze profile update and delete routes are rate limited', function (string $method, string $routeName) {
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->admin)->{$method}(route($routeName), ['password' => 'wrong', 'current_password' => 'wrong', 'name' => 'Ada', 'email' => 'x@example.test']);
    }

    $this->actingAs($this->admin)
        ->{$method}(route($routeName), ['password' => 'wrong'])
        ->assertTooManyRequests();
})->with([
    'update' => ['patch', 'admin.profile.update'],
    'delete' => ['delete', 'admin.profile.destroy'],
]);

test('changing the password signs out other sessions but keeps the current one', function () {
    // Session B: an earlier session whose stored password hash predates the change.
    $this->actingAs($this->admin)->get(route('admin.banner'))->assertOk();
    $sessionBHash = session('password_hash_web');
    expect($sessionBHash)->not->toBeNull();

    // Session A changes the password.
    $this->actingAs($this->admin)->put(route('admin.profile.password.update'), [
        'current_password' => 'password',
        'password' => 'N3w!Password',
        'password_confirmation' => 'N3w!Password',
    ])->assertSessionHasNoErrors();

    // Session A keeps working.
    $this->actingAs($this->admin->fresh())->get(route('admin.banner'))->assertOk();

    // Session B is logged out on its next request.
    $this->flushSession();
    $this->withSession(['password_hash_web' => $sessionBHash])
        ->actingAs($this->admin->fresh())
        ->get(route('admin.banner'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('an image that cannot be processed gives a validation error instead of a server error', function () {
    Exceptions::fake();

    $this->mock(ImageUploadService::class, function (MockInterface $mock) {
        $mock->shouldReceive('storeCropped')->andThrow(new RuntimeException('GD could not decode the file.'));
        $mock->shouldNotReceive('delete');
    });

    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload([
            'profile_image' => UploadedFile::fake()->image('broken.jpg', 500, 500),
        ]))
        ->assertSessionHasErrors(['profile_image' => 'The profile image could not be processed. Please try a different image.']);

    expect($this->admin->fresh()->profile_image)->toBeNull();
    Exceptions::assertReported(RuntimeException::class);
});

test('a failed save keeps the old image and removes the new upload', function () {
    Exceptions::fake();

    $this->actingAs($this->admin)->patch(route('admin.profile.settings.update'), profilePayload([
        'profile_image' => UploadedFile::fake()->image('first.png', 500, 500),
    ]));
    $oldPath = $this->admin->fresh()->profile_image;

    User::saving(function (User $user) {
        if ($user->isDirty('profile_image')) {
            throw new RuntimeException('Database unavailable.');
        }
    });

    $this->actingAs($this->admin->fresh())
        ->patch(route('admin.profile.settings.update'), profilePayload([
            'profile_image' => UploadedFile::fake()->image('second.png', 500, 500),
        ]))
        ->assertServerError();

    expect($this->admin->fresh()->profile_image)->toBe($oldPath)
        ->and(Storage::disk('public')->allFiles())->toBe([$oldPath]);
});

test('images larger than the source dimension cap are rejected', function () {
    $this->actingAs($this->admin)
        ->patch(route('admin.profile.settings.update'), profilePayload([
            'profile_image' => UploadedFile::fake()->image('huge.jpg', ImageUploadService::MAX_SOURCE_DIMENSION + 1, 200),
        ]))
        ->assertSessionHasErrors('profile_image');
});

test('password changes are rate limited', function () {
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->admin)->put(route('admin.profile.password.update'), ['current_password' => 'wrong']);
    }

    $this->actingAs($this->admin)
        ->put(route('admin.profile.password.update'), ['current_password' => 'wrong'])
        ->assertTooManyRequests();
});
