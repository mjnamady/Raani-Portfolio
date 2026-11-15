<?php

namespace App\Policies;

use App\Models\ProjectCategory;
use App\Models\User;

/**
 * Project categories are managed by the site owner only.
 */
class ProjectCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, ProjectCategory $projectCategory): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, ProjectCategory $projectCategory): bool
    {
        return (bool) $user->is_admin;
    }
}
