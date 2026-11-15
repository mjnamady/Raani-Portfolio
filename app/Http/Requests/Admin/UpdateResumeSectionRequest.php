<?php

namespace App\Http\Requests\Admin;

use App\Models\ResumeEntry;
use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Section settings for the Education or Experience list: title, visibility and (experience only) the side image.
 */
class UpdateResumeSectionRequest extends FormRequest
{
    /**
     * The app's own size limit for the experience photo; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_IMAGE_KILOBYTES = 4096;

    public function isExperience(): bool
    {
        return $this->routeIs('admin.experience.*');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', ResumeEntry::class);
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
        $rules = [
            'title' => ['required', 'string', 'max:60'],
            'is_active' => ['boolean'],
        ];

        if ($this->isExperience()) {
            $rules += [
                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES),
                    ImageUploadService::dimensionsRule(300),
                ],
                'image_alt' => ['required', 'string', 'max:150'],
            ];
        }

        return $rules;
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
            'image.max' => "The image may not be larger than {$limit}.",
            // PHP drops files above its upload limit before Laravel sees them.
            'image.uploaded' => "The image could not be uploaded. Please choose a file smaller than {$limit}.",
            'image.dimensions' => ImageUploadService::dimensionsMessage('The image', 300),
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['image_alt' => 'image description'];
    }
}
