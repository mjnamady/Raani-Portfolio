<?php

namespace App\Http\Requests\Admin;

use App\Models\SkillBar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The About page's skill column titles and switches (ABT-5, A-5).
 */
class UpdateAboutPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('viewAny', SkillBar::class);
    }

    /**
     * Normalise the switches and trim the titles. Non-string input is passed through so
     * the "string" rule rejects it.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (array_keys(SkillBar::GROUPS) as $group) {
            $title = $this->input("{$group}_title");
            $merge["{$group}_title"] = is_string($title) ? trim($title) : $title;
            $merge["{$group}_active"] = $this->boolean("{$group}_active");
        }

        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(SkillBar::GROUPS) as $group) {
            $rules["{$group}_title"] = ['required', 'string', 'max:60'];
            $rules["{$group}_active"] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'design_title' => 'design column title',
            'development_title' => 'development column title',
        ];
    }
}
