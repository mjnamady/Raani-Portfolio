<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

/**
 * Skills are managed by the site owner only.
 */
class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Skill $skill): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Skill $skill): bool
    {
        return (bool) $user->is_admin;
    }
}
