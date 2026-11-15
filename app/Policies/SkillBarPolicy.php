<?php

namespace App\Policies;

use App\Models\SkillBar;
use App\Models\User;

/**
 * The About page content is managed by the site owner only.
 */
class SkillBarPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, SkillBar $skillBar): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, SkillBar $skillBar): bool
    {
        return (bool) $user->is_admin;
    }
}
