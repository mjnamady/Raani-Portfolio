<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Blog posts are managed by the site owner only.
 */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Post $post): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Post $post): bool
    {
        return (bool) $user->is_admin;
    }
}
