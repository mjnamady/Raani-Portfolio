<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Projects (and their gallery images) are managed by the site owner only.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Project $project): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Project $project): bool
    {
        return (bool) $user->is_admin;
    }
}
