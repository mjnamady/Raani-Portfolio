<?php

namespace App\Http\Requests\Admin;

use App\Support\FontAwesomeIcons;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicesAreaRequest extends FormRequest
{
    public const MAX_SERVICES = 8;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Normalise checkboxes and the icon class, keeping each row's original key so
     * errors line up with the row the owner edited.
     */
    protected function prepareForValidation(): void
    {
        // The editor always sends "services" (an empty hidden field when every card is removed).
        // A request without it is left alone so the "present" rule rejects it instead of
        // reading it as "remove every card".
        if (! $this->has('services')) {
            return;
        }

        $services = collect(is_array($this->input('services')) ? $this->input('services') : [])
            ->filter(fn ($row) => is_array($row))
            ->map(fn (array $row) => array_merge($row, [
                'icon' => trim(preg_replace('/\s+/', ' ', (string) ($row['icon'] ?? ''))),
                'is_active' => filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]))
            ->all();

        $this->merge(['services' => $services]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'services' => ['present', 'array', 'max:'.self::MAX_SERVICES],
            // Only cards shown in this editor (featured services) can be updated from it.
            'services.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('services', 'id')->where('is_featured', true)],
            'services.*.title' => ['required', 'string', 'max:80'],
            'services.*.icon' => [
                'required',
                'string',
                'max:60',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! FontAwesomeIcons::isValid((string) $value)) {
                        $fail('Choose an icon from the list, e.g. "fa-light fa-pen-ruler".');
                    }
                },
            ],
            'services.*.project_count' => ['required', 'integer', 'min:0', 'max:9999999'],
            'services.*.project_count_label' => ['required', 'string', 'max:30'],
            'services.*.is_active' => ['boolean'],
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
            'services.present' => 'The service cards were not submitted. Reload the page and try again.',
            'services.max' => 'The services area can show at most '.self::MAX_SERVICES.' cards.',
            'services.*.id.exists' => 'This service no longer exists. Reload the page and try again.',
            'services.*.id.distinct' => 'The same service was submitted twice. Reload the page and try again.',
            'services.*.title.required' => 'Enter a title for this service.',
            'services.*.icon.required' => 'Choose an icon for this service.',
            'services.*.project_count.required' => 'Enter the number of projects.',
            'services.*.project_count.integer' => 'The number of projects must be a whole number.',
            'services.*.project_count.max' => 'The number of projects may not be greater than 9,999,999.',
            'services.*.project_count_label.required' => 'Enter the label shown after the number, e.g. "Projects".',
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
            'services.*.title' => 'title',
            'services.*.project_count_label' => 'label',
        ];
    }
}
