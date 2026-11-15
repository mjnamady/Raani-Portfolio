<?php

namespace App\Http\Requests\Admin;

use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add one or more gallery images to a project (PRJ-5).
 */
class StoreProjectImagesRequest extends FormRequest
{
    public const MAX_PER_UPLOAD = 10;

    public const MAX_PER_PROJECT = 24;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('project'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $room = max(0, self::MAX_PER_PROJECT - $this->route('project')->images()->count());

        return [
            'images' => ['required', 'array', 'min:1', 'max:'.min(self::MAX_PER_UPLOAD, $room)],
            'images.*' => StoreProjectRequest::imageRules(300, 200),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimit::label(StoreProjectRequest::MAX_IMAGE_KILOBYTES);

        return [
            'images.required' => 'Choose at least one image to add to the gallery.',
            'images.max' => 'You can add up to '.self::MAX_PER_UPLOAD.' images at a time, and a gallery holds at most '.self::MAX_PER_PROJECT.'.',
            'images.*.max' => "Each image may not be larger than {$limit}.",
            'images.*.uploaded' => "An image could not be uploaded. Please choose files smaller than {$limit}.",
            'images.*.dimensions' => ImageUploadService::dimensionsMessage('Each image', 300, 200),
            'images.*.image' => 'Each file must be a JPG, PNG or WebP image.',
            'images.*.mimes' => 'Each file must be a JPG, PNG or WebP image.',
        ];
    }
}
