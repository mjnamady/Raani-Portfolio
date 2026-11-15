<?php

namespace App\Policies;

use App\Models\ResumeEntry;
use App\Models\User;

/**
 * Education and experience entries are managed by the site owner only.
 */
class ResumeEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, ResumeEntry $resumeEntry): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, ResumeEntry $resumeEntry): bool
    {
        return (bool) $user->is_admin;
    }
}
