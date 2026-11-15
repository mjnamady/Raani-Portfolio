<?php

namespace App\Http\Requests\Admin;

/**
 * Edit a project; the same rules as creating one (the slug check ignores this project).
 */
class UpdateProjectRequest extends StoreProjectRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('project'));
    }
}
