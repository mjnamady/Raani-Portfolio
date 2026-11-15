<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCounterAreaRequest extends FormRequest
{
    public const MAX_COUNTERS = 4;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Normalise the checkbox and drop counter rows the owner left completely empty.
     */
    protected function prepareForValidation(): void
    {
        $counters = collect($this->input('counters', []))
            ->filter(fn ($row) => is_array($row))
            ->reject(fn (array $row) => blank($row['value'] ?? null) && blank($row['label'] ?? null) && blank($row['suffix'] ?? null))
            // Keep the original row keys so errors line up with the row the owner edited.
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'counters' => $counters,
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
            'is_active' => ['boolean'],

            'highlight' => ['required', 'array'],
            'highlight.value' => ['required', 'integer', 'min:0', 'max:9999999'],
            'highlight.suffix' => ['nullable', 'string', 'max:5'],
            'highlight.label' => ['required', 'string', 'max:60'],
            'highlight.description' => ['nullable', 'string', 'max:300'],

            'counters' => ['array', 'max:'.self::MAX_COUNTERS],
            'counters.*.value' => ['required', 'integer', 'min:0', 'max:9999999'],
            'counters.*.suffix' => ['nullable', 'string', 'max:5'],
            'counters.*.label' => ['required', 'string', 'max:60'],
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
            'highlight.value.required' => 'Enter the highlight number.',
            'highlight.value.integer' => 'The highlight number must be a whole number.',
            'highlight.value.max' => 'The highlight number may not be greater than 9,999,999.',
            'highlight.label.required' => 'Enter a label for the highlight.',
            'counters.max' => 'The counter area can show at most 4 counters.',
            'counters.*.value.required' => 'Enter a number for this counter.',
            'counters.*.value.integer' => 'The counter must be a whole number.',
            'counters.*.value.max' => 'The counter may not be greater than 9,999,999.',
            'counters.*.label.required' => 'Enter a label for this counter.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'highlight.suffix' => 'suffix',
            'highlight.label' => 'label',
            'highlight.description' => 'description',
            'counters.*.suffix' => 'suffix',
            'counters.*.label' => 'label',
        ];
    }
}
