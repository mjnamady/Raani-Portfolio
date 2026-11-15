<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use App\Services\ImageUploadService;
use App\Support\RichText;
use App\Support\UploadLimit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Create a project (PRJ-1). UpdateProjectRequest reuses these rules.
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * The app's own size limit for card and cover images; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_IMAGE_KILOBYTES = 4096;

    public const MAX_FEATURES = 12;

    public const MAX_TAGS = 10;

    /**
     * Largest sanitized content that fits the TEXT column, with a little headroom.
     */
    public const MAX_CONTENT_BYTES = 65000;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Project::class);
    }

    /**
     * Normalise the slug, checkboxes, feature lines and the comma-separated tags.
     */
    protected function prepareForValidation(): void
    {
        $features = collect(is_array($this->input('features')) ? $this->input('features') : [])
            ->filter(fn ($line) => is_string($line))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        // Non-string input (e.g. tags[]=x) is left for the rules to reject instead of crashing here.
        $tags = collect(explode(',', is_string($this->input('tags')) ? $this->input('tags') : ''))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => Str::slug($tag))
            ->values()
            ->all();

        $this->merge([
            'slug' => static::normaliseString($this->input('slug'), fn (string $slug) => Str::slug($slug)),
            'features' => $features,
            'tags' => $tags,
            'is_featured' => $this->boolean('is_featured'),
            'external_url' => static::normaliseString($this->input('external_url'), fn (string $url) => trim($url)),
        ]);
    }

    /**
     * Apply $normalise to a filled string; empty becomes null, and any other type is passed
     * through unchanged so the "string" rule rejects it with a validation error.
     */
    protected static function normaliseString(mixed $value, Closure $normalise): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return filled($value) ? $normalise($value) : null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($this->route('project'))],
            'tagline' => ['nullable', 'string', 'max:80'],
            'category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'client_name' => ['nullable', 'string', 'max:80'],
            'project_date' => ['nullable', 'date'],
            'content' => [
                'nullable', 'string', 'max:60000',
                // Sanitizing escapes characters (& becomes &amp;), so check what will actually be stored (TEXT = 65,535 bytes).
                function (string $attribute, mixed $value, Closure $fail) {
                    if (strlen((string) RichText::clean($value)) > self::MAX_CONTENT_BYTES) {
                        $fail('The content is too long. Please shorten it a little.');
                    }
                },
            ],
            'features' => ['array', 'max:'.self::MAX_FEATURES],
            'features.*' => ['string', 'max:80'],
            'tags' => ['array', 'max:'.self::MAX_TAGS],
            'tags.*' => [
                'string', 'max:40',
                // Transliteration can make a slug longer than the name ("Щ" becomes "shch"); the column holds 60.
                function (string $attribute, mixed $value, Closure $fail) {
                    if (strlen(Str::slug((string) $value)) > 60) {
                        $fail('The tag "'.$value.'" is too long. Please use a shorter tag.');
                    }
                },
            ],
            // Only web links: no javascript:, data: or other schemes.
            'external_url' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'is_featured' => ['boolean'],
            'status' => ['required', Rule::in([Project::STATUS_DRAFT, Project::STATUS_PUBLISHED])],
            // The column is a TIMESTAMP, which ends in January 2038.
            'published_at' => ['nullable', 'date', 'after:2000-01-01', 'before:2038-01-01'],
            'card_image' => ['nullable', ...static::imageRules(300)],
            'card_image_alt' => ['nullable', 'string', 'max:150'],
            'cover_image' => ['nullable', ...static::imageRules(600, 260)],
            'cover_image_alt' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * Upload rules for a project image (without "nullable").
     *
     * @return list<string>
     */
    public static function imageRules(int $minWidth, ?int $minHeight = null): array
    {
        return [
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES),
            ImageUploadService::dimensionsRule($minWidth, $minHeight),
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
            'slug.alpha_dash' => 'The URL slug may only contain letters, numbers, dashes and underscores.',
            'slug.unique' => 'Another project already uses this URL slug.',
            'published_at.before' => 'Choose a publish date before 2038.',
            'published_at.after' => 'Choose a publish date after the year 2000.',
            'external_url.url' => 'Enter a full address starting with http:// or https://.',
            'features.max' => 'A project can list at most '.self::MAX_FEATURES.' features.',
            'tags.max' => 'A project can have at most '.self::MAX_TAGS.' tags.',
            'card_image.max' => "The card image may not be larger than {$limit}.",
            'card_image.uploaded' => "The card image could not be uploaded. Please choose a file smaller than {$limit}.",
            'card_image.dimensions' => ImageUploadService::dimensionsMessage('The card image', 300),
            'cover_image.max' => "The cover image may not be larger than {$limit}.",
            'cover_image.uploaded' => "The cover image could not be uploaded. Please choose a file smaller than {$limit}.",
            'cover_image.dimensions' => ImageUploadService::dimensionsMessage('The cover image', 600, 260),
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
            'slug' => 'URL slug',
            'category_id' => 'category',
            'client_name' => 'client / author',
            'external_url' => 'project link',
            'published_at' => 'publish date',
            'features.*' => 'feature',
            'tags.*' => 'tag',
            'card_image_alt' => 'card image description',
            'cover_image_alt' => 'cover image description',
        ];
    }
}
