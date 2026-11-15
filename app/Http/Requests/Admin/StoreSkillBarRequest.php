<?php

namespace App\Http\Requests\Admin;

use App\Models\SkillBar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add a skill bar to one of the About page columns (ABT-5).
 */
class StoreSkillBarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', SkillBar::class);
    }

    /**
     * Trim the name. Non-string input is passed through so the "string" rule rejects it.
     * Errors go to a bag per column ("addBarDesign"), so they show under the right "add" form.
     */
    protected function prepareForValidation(): void
    {
        $group = $this->input('group');
        $this->errorBag = 'addBar'.(is_string($group) && array_key_exists($group, SkillBar::GROUPS) ? ucfirst($group) : '');

        $name = $this->input('name');

        $this->merge(['name' => is_string($name) ? trim(preg_replace('/\s+/u', ' ', $name)) : $name]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group' => ['required', 'string', Rule::in(array_keys(SkillBar::GROUPS))],
            'name' => ['required', 'string', 'max:60'],
            'percent' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'percent.integer' => 'The percentage must be a whole number from 0 to 100.',
            'percent.min' => 'The percentage must be a whole number from 0 to 100.',
            'percent.max' => 'The percentage must be a whole number from 0 to 100.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'skill name', 'percent' => 'percentage', 'group' => 'column'];
    }
}
