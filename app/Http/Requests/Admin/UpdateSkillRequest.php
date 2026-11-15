<?php

namespace App\Http\Requests\Admin;

/**
 * Edit a skill; the same rules as adding one.
 */
class UpdateSkillRequest extends StoreSkillRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('skill'));
    }
}
