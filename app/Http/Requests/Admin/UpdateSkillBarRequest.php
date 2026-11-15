<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rename a skill bar or change its percentage; the column stays the same.
 */
class UpdateSkillBarRequest extends StoreSkillBarRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('skillBar'));
    }

    /**
     * Trim the name. Errors go to their own bag; each row's form also sends a hidden "bar_id",
     * so the page knows which row to show them on.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->errorBag = 'updateBar';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return collect(parent::rules())->except('group')->all();
    }
}
