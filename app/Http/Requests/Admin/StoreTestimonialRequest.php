<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add a testimonial (TST-1). UpdateTestimonialRequest reuses these rules.
 */
class StoreTestimonialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Testimonial::class);
    }

    /**
     * Normalise the checkbox and collapse the quote onto one line (the card shows it as one paragraph).
     * Non-string input is passed through so the "string" rule rejects it.
     */
    protected function prepareForValidation(): void
    {
        $quote = $this->input('quote');

        $this->merge([
            'quote' => is_string($quote) ? trim(preg_replace('/\s+/u', ' ', $quote)) : $quote,
            'is_active' => $this->boolean('is_active'),
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
            'quote' => ['required', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:80'],
            'role' => ['nullable', 'string', 'max:80'],
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
        return ['role' => 'role or company'];
    }
}
