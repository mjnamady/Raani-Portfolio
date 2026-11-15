<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

/**
 * Supported-company logos are managed by the site owner only.
 */
class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Client $client): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Client $client): bool
    {
        return (bool) $user->is_admin;
    }
}
