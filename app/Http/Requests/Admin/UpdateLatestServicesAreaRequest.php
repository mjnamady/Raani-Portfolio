<?php

namespace App\Http\Requests\Admin;

use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLatestServicesAreaRequest extends FormRequest
{
    public const MAX_ITEMS = 3;

    /**
     * The app's own size limit for the side image; PHP's upload limit may lower it (see UploadLimit).
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
     * Normalise the checkbox and drop item rows the owner left completely empty.
     */
    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->filter(fn ($row) => is_array($row))
            ->reject(fn (array $row) => blank($row['title'] ?? null) && blank($row['description'] ?? null))
            // Keep the original row keys so errors line up with the row the owner edited.
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'items' => $items,
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

            'eyebrow' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES),
                ImageUploadService::dimensionsRule(300),
            ],
            'image_alt' => ['required', 'string', 'max:150'],

            'items' => ['array', 'max:'.self::MAX_ITEMS],
            'items.*.title' => ['required', 'string', 'max:120'],
            'items.*.description' => ['required', 'string', 'max:300'],
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
            'image.max' => "The side image may not be larger than {$limit}.",
            // PHP drops files above its upload limit before Laravel sees them.
            'image.uploaded' => "The side image could not be uploaded. Please choose a file smaller than {$limit}.",
            'image.dimensions' => ImageUploadService::dimensionsMessage('The side image', 300),
            'items.max' => 'The section can show at most 3 services.',
            'items.*.title.required' => 'Enter a title for this service.',
            'items.*.description.required' => 'Enter a description for this service.',
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
            'subtitle' => 'description',
            'image_alt' => 'image description',
            'items.*.title' => 'title',
            'items.*.description' => 'description',
        ];
    }
}
