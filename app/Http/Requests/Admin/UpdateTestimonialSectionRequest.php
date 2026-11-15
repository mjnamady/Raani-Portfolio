<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Big title and visibility of the home Testimonial section (A-5).
 */
class UpdateTestimonialSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('viewAny', Testimonial::class);
    }

    /**
     * Normalise the checkbox.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Shown at 180px, so keep it to a word or two.
            'title' => ['required', 'string', 'max:40'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['title' => 'section title'];
    }
}
