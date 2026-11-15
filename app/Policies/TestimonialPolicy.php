<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

/**
 * Testimonials are managed by the site owner only.
 */
class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return (bool) $user->is_admin;
    }
}
