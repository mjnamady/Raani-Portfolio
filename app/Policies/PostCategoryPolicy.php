<?php

namespace App\Policies;

use App\Models\PostCategory;
use App\Models\User;

/**
 * Blog categories are managed by the site owner only.
 */
class PostCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, PostCategory $category): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, PostCategory $category): bool
    {
        return (bool) $user->is_admin;
    }
}
