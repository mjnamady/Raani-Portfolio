<?php

namespace App\Policies;

use App\Models\User;

/**
 * Site settings are managed by the site owner only.
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
