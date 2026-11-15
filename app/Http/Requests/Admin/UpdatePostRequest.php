<?php

namespace App\Http\Requests\Admin;

/**
 * Edit a blog post; the same rules as writing one (the slug check ignores this post).
 */
class UpdatePostRequest extends StorePostRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('post'));
    }
}
