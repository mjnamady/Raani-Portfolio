<?php

namespace App\Http\Requests\Admin;

use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerSectionRequest extends FormRequest
{
    public const MAX_STATS = 3;

    /**
     * The app's own size limit for the portrait; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_IMAGE_KILOBYTES = 4096;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Normalise the checkbox and drop stat rows the owner left completely empty.
     */
    protected function prepareForValidation(): void
    {
        $stats = collect($this->input('stats', []))
            ->filter(fn ($row) => is_array($row))
            ->reject(fn (array $row) => blank($row['value'] ?? null) && blank($row['label'] ?? null) && blank($row['suffix'] ?? null))
            // Keep the original row keys so errors line up with the row the owner edited.
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'stats' => $stats,
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
            'greeting' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:80'],
            'role' => ['required', 'string', 'max:80'],

            'biography_heading' => ['required', 'string', 'max:40'],
            'biography' => ['required', 'string', 'max:600'],

            // The contact details themselves are edited on the Contact Page (site-wide settings).
            'contact_heading' => ['required', 'string', 'max:40'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES),
                ImageUploadService::dimensionsRule(300),
            ],
            'image_alt' => ['required', 'string', 'max:150'],

            'is_active' => ['boolean'],

            'stats' => ['array', 'max:'.self::MAX_STATS],
            'stats.*.value' => ['required', 'integer', 'min:0', 'max:9999999'],
            'stats.*.suffix' => ['nullable', 'string', 'max:5'],
            'stats.*.label' => ['required', 'string', 'max:60'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimit::label(self::MAX_IMAGE_KILOBYTES);

        return [
            'image.max' => "The banner image may not be larger than {$limit}.",
            // PHP drops files above its upload limit before Laravel sees them.
            'image.uploaded' => "The banner image could not be uploaded. Please choose a file smaller than {$limit}.",
            'image.dimensions' => ImageUploadService::dimensionsMessage('The banner image', 300),
            'stats.max' => 'The banner can show at most 3 counters.',
            'stats.*.value.required' => 'Enter a number for this counter.',
            'stats.*.value.integer' => 'The counter must be a whole number.',
            'stats.*.value.max' => 'The counter may not be greater than 9,999,999.',
            'stats.*.label.required' => 'Enter a label for this counter.',
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
            'image_alt' => 'image description',
            'stats.*.suffix' => 'suffix',
            'stats.*.label' => 'label',
        ];
    }
}
