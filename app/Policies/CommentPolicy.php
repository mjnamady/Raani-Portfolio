<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

/**
 * Only the site owner moderates comments. Visitors post through the public form, which needs no account.
 */
class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Comment $comment): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return (bool) $user->is_admin;
    }
}
