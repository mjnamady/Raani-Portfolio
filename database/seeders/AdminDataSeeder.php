<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminDataSeeder extends Seeder
{
    /**
     * Seed the portfolio owner's admin account from config/admin.php.
     *
     * This is a single-owner site: once an admin exists the seeder changes nothing,
     * so reseeding can neither reset the password nor create a second admin.
     */
    public function run(): void
    {
        $email = Str::lower(trim((string) config('admin.email')));
        $password = config('admin.password');

        if (app()->isProduction() && (blank($email) || blank($password))) {
            $this->command?->error('Set ADMIN_EMAIL and ADMIN_PASSWORD before seeding the admin account in production.');

            return;
        }

        if (filled($password) && ! $this->isStrong($password)) {
            if (app()->isProduction()) {
                $this->command?->error('ADMIN_PASSWORD must be at least 12 characters with upper and lower case letters, numbers and symbols.');

                return;
            }

            $this->command?->warn('ADMIN_PASSWORD is weak; never use it outside local development.');
        }

        if (User::where('is_admin', true)->exists()) {
            $this->command?->info('An admin account already exists; left unchanged.');

            return;
        }

        $email = filled($email) ? $email : 'admin@portfolio.test';

        if ($existing = User::where('email', $email)->first()) {
            $existing->forceFill(['is_admin' => true])->save();
            $this->command?->info("Existing account {$email} marked as admin; password left unchanged.");

            return;
        }

        $username = config('admin.username') ?: 'owner';

        if (User::where('username', $username)->exists()) {
            $this->command?->error("The username {$username} is already taken; set a different ADMIN_USERNAME.");

            return;
        }

        $generated = blank($password);
        $password = $generated ? Str::password(16) : $password;

        $admin = User::create([
            'name' => config('admin.name'),
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $admin->forceFill([
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        if ($generated) {
            $this->command?->warn("Admin account {$email} created with generated password: {$password}");
        }
    }

    /**
     * Determine whether the configured password meets the production strength rules.
     */
    protected function isStrong(string $password): bool
    {
        return Validator::make(
            ['password' => $password],
            ['password' => [Password::min(12)->letters()->mixedCase()->numbers()->symbols()]],
        )->passes();
    }
}
