<?php

namespace App\Http\Requests\Admin;

use App\Models\Client;
use App\Services\ImageUploadService;
use App\Support\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Add a supported-company logo (CLT-1). UpdateClientRequest reuses these rules with an optional logo.
 */
class StoreClientRequest extends FormRequest
{
    /**
     * The app's own size limit for logos; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_LOGO_KILOBYTES = 2048;

    public const MIN_LOGO_WIDTH = 60;

    public const MIN_LOGO_HEIGHT = 20;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Client::class);
    }

    /**
     * Normalise the checkbox and treat an empty website as none.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            // Non-string input is left for the "string" rule to reject instead of crashing here.
            'url' => is_string($this->input('url')) ? (filled($this->input('url')) ? trim($this->input('url')) : null) : $this->input('url'),
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
            'name' => ['required', 'string', 'max:80'],
            // Only web links: no javascript:, data: or other schemes.
            'url' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'is_active' => ['boolean'],
            'logo' => ['required', ...static::logoRules()],
        ];
    }

    /**
     * Upload rules for a logo (without "required"/"nullable"). SVG is not accepted: it can carry scripts.
     *
     * @return list<string>
     */
    public static function logoRules(): array
    {
        return [
            'image',
            'mimes:png,webp,jpg,jpeg',
            'max:'.UploadLimit::kilobytes(self::MAX_LOGO_KILOBYTES),
            ImageUploadService::dimensionsRule(self::MIN_LOGO_WIDTH, self::MIN_LOGO_HEIGHT),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limit = UploadLimit::label(self::MAX_LOGO_KILOBYTES);

        return [
            'url.url' => 'Enter a full website address starting with http:// or https://.',
            'logo.mimes' => 'The logo must be a PNG, WebP or JPG image. SVG files are not accepted.',
            'logo.max' => "The logo may not be larger than {$limit}.",
            'logo.uploaded' => "The logo could not be uploaded. Please choose a file smaller than {$limit}.",
            'logo.dimensions' => ImageUploadService::dimensionsMessage('The logo', self::MIN_LOGO_WIDTH, self::MIN_LOGO_HEIGHT),
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'company name', 'url' => 'website'];
    }
}
