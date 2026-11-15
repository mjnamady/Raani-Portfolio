<?php

namespace App\Http\Requests\Admin;

use App\Models\PostCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Add a blog category (BLG-2). UpdatePostCategoryRequest reuses these rules.
 */
class StorePostCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', PostCategory::class);
    }

    /**
     * Normalise the slug.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Non-string input is left for the "string" rule to reject instead of crashing here.
            'slug' => is_string($this->input('slug')) ? (filled($this->input('slug')) ? Str::slug($this->input('slug')) : null) : $this->input('slug'),
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
            'name' => ['required', 'string', 'max:60'],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('post_categories', 'slug')->ignore($this->route('category'))],
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
            'slug.unique' => 'Another category already uses this URL slug.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'category name', 'slug' => 'URL slug'];
    }
}
