<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Services\ImageUploadService;
use App\Support\RichText;
use App\Support\UploadLimit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Write a blog post (BLG-1). UpdatePostRequest reuses these rules.
 */
class StorePostRequest extends FormRequest
{
    /**
     * The app's own size limit for the featured image; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_IMAGE_KILOBYTES = 4096;

    public const MAX_TAGS = 10;

    /**
     * Largest sanitized body that fits the TEXT column, with a little headroom.
     */
    public const MAX_BODY_BYTES = 65000;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Post::class);
    }

    /**
     * Normalise the slug, the trimmed text fields and the comma-separated tags.
     * Non-string input (e.g. tags[]=x) is left for the rules to reject instead of crashing here.
     */
    protected function prepareForValidation(): void
    {
        $tags = collect(explode(',', is_string($this->input('tags')) ? $this->input('tags') : ''))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => Str::slug($tag))
            ->values()
            ->all();

        $this->merge([
            'slug' => static::normaliseString($this->input('slug'), fn (string $slug) => Str::slug($slug)),
            'excerpt' => static::normaliseString($this->input('excerpt'), fn (string $text) => trim(preg_replace('/\s+/u', ' ', $text))),
            'meta_title' => static::normaliseString($this->input('meta_title'), fn (string $text) => trim($text)),
            'meta_description' => static::normaliseString($this->input('meta_description'), fn (string $text) => trim(preg_replace('/\s+/u', ' ', $text))),
            'tags' => is_array($this->input('tags')) ? $this->input('tags') : $tags,
            'allow_comments' => $this->boolean('allow_comments'),
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
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($this->route('post'))],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'body' => [
                'nullable', 'required_if:status,'.Post::STATUS_PUBLISHED, 'string', 'max:60000',
                // Sanitizing escapes characters (& becomes &amp;), so check what will actually be stored (TEXT = 65,535 bytes).
                function (string $attribute, mixed $value, Closure $fail) {
                    if (is_string($value) && strlen((string) RichText::clean($value)) > self::MAX_BODY_BYTES) {
                        $fail('The post is too long. Please shorten it a little.');
                    }
                },
            ],
            'tags' => ['array', 'max:'.self::MAX_TAGS],
            'tags.*' => [
                'string', 'max:40',
                // Transliteration can make a slug longer than the name ("Щ" becomes "shch"); the column holds 60.
                function (string $attribute, mixed $value, Closure $fail) {
                    if (is_string($value) && strlen(Str::slug($value)) > 60) {
                        $fail('The tag "'.$value.'" is too long. Please use a shorter tag.');
                    }
                },
            ],
            'author_name' => ['required', 'string', 'max:80'],
            'allow_comments' => ['boolean'],
            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            // Scheduled posts are deferred (MVP §2.2), so the date may be back-dated but not in the future.
            'published_at' => ['nullable', 'date', 'after:2000-01-01', 'before_or_equal:now'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES), ImageUploadService::dimensionsRule(600, 310)],
            'featured_image_alt' => ['nullable', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    /**
     * A published post needs a featured image: the blog and home cards are built around it.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $post = $this->route('post');
                $hasImage = $this->hasFile('featured_image') || ($post instanceof Post && filled($post->featured_image));

                if ($this->input('status') === Post::STATUS_PUBLISHED && ! $hasImage && ! $validator->errors()->has('featured_image')) {
                    $validator->errors()->add('featured_image', 'Add a featured image before publishing; the blog cards need one.');
                }
            },
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
            'slug.unique' => 'Another post already uses this URL slug.',
            'body.required_if' => 'Write the post before publishing it (or save it as a draft).',
            'published_at.before_or_equal' => 'The publish date can not be in the future; scheduled posts are not available yet.',
            'published_at.after' => 'Choose a publish date after the year 2000.',
            'tags.max' => 'A post can have at most '.self::MAX_TAGS.' tags.',
            'featured_image.max' => "The featured image may not be larger than {$limit}.",
            'featured_image.uploaded' => "The featured image could not be uploaded. Please choose a file smaller than {$limit}.",
            'featured_image.dimensions' => ImageUploadService::dimensionsMessage('The featured image', 600, 310),
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
            'body' => 'post',
            'author_name' => 'author',
            'published_at' => 'publish date',
            'tags.*' => 'tag',
            'featured_image_alt' => 'image description',
            'meta_title' => 'SEO title',
            'meta_description' => 'SEO description',
        ];
    }
}
