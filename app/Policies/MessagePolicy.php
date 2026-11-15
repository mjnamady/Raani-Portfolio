<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

/**
 * The inbox is read and managed by the site owner only.
 */
class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Message $message): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Message $message): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Message $message): bool
    {
        return (bool) $user->is_admin;
    }
}
