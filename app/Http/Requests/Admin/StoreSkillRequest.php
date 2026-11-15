<?php

namespace App\Http\Requests\Admin;

use App\Models\Skill;
use App\Support\FontAwesomeIcons;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add a skill (SKL-1). UpdateSkillRequest reuses these rules.
 */
class StoreSkillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Skill::class);
    }

    /**
     * Normalise checkboxes, the icon class and the count label.
     * Non-string input is passed through so the "string" rule rejects it.
     */
    protected function prepareForValidation(): void
    {
        $icon = $this->input('icon');
        $label = $this->input('done_label');

        $this->merge([
            'icon' => is_string($icon) ? trim(preg_replace('/\s+/', ' ', $icon)) : $icon,
            'done_label' => is_string($label) && trim($label) === '' ? 'Done' : (is_string($label) ? trim($label) : ($label ?? 'Done')),
            'is_active' => $this->boolean('is_active'),
            'is_highlighted' => $this->boolean('is_highlighted'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'icon' => [
                'required',
                'string',
                'max:60',
                function (string $attribute, mixed $value, Closure $fail) {
                    // Non-strings are already rejected by the "string" rule; never cast an array here.
                    if (is_string($value) && ! FontAwesomeIcons::isValid($value)) {
                        $fail('Choose an icon from the list, e.g. "fa-light fa-pen-nib".');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:80'],
            'done_count' => ['required', 'integer', 'min:0', 'max:9999999'],
            'done_label' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:300'],
            'is_active' => ['boolean'],
            'is_highlighted' => ['boolean'],
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
            'done_count.required' => 'Enter how many you have done (0 or more).',
            'done_count.integer' => 'The count must be a whole number.',
            'done_count.max' => 'The count may not be greater than 9,999,999.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['done_count' => 'count', 'done_label' => 'count label'];
    }
}
