<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use App\Rules\ValidUtf8;
use App\Services\ImageUploadService;
use App\Support\InvisibleCharacters;
use App\Support\SiteSettings;
use App\Support\UploadLimit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Site Settings (M3): identity, social links, menu panel and footer.
 */
class UpdateSiteSettingsRequest extends FormRequest
{
    /**
     * Text fields and the setting each one is saved to.
     */
    public const TEXT_FIELDS = [
        'site_name' => 'site.name',
        'site_tagline' => 'site.tagline',
        'site_copyright' => 'site.copyright',
        'offcanvas_heading' => 'offcanvas.heading',
        'offcanvas_bio' => 'offcanvas.bio',
        'offcanvas_social_label' => 'offcanvas.social_label',
        'offcanvas_phone_label' => 'offcanvas.phone_label',
        'offcanvas_email_label' => 'offcanvas.email_label',
        'offcanvas_address_label' => 'offcanvas.address_label',
        'footer_tagline_highlight' => 'footer.tagline_highlight',
        'footer_tagline' => 'footer.tagline',
        'footer_links_title' => 'footer.links_title',
        'footer_contact_title' => 'footer.contact_title',
    ];

    /**
     * The app's own size limit for each image; PHP's upload limit may lower it (see UploadLimit).
     */
    public const MAX_IMAGE_KILOBYTES = 2048;

    /**
     * Smallest accepted upload per image, in pixels.
     *
     * @var array<string, array{0: int, 1: int}>
     */
    public const MIN_IMAGE_SIZES = [
        'logo' => [60, 20],
        'logo_light' => [60, 20],
        'favicon' => [32, 32],
        'offcanvas_image' => [100, 100],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', Setting::class);
    }

    /**
     * Trim the text, normalise the "use the theme image" switches and drop empty footer link
     * rows and social links. Non-string input is passed through so the rules reject it.
     */
    protected function prepareForValidation(): void
    {
        $trim = fn (mixed $value) => is_string($value) ? InvisibleCharacters::strip($value) : $value;
        $merge = [];

        foreach (array_keys(self::TEXT_FIELDS) as $field) {
            $merge[$field] = $trim($this->input($field));
        }

        foreach (array_keys(SiteSettings::IMAGES) as $image) {
            $merge["reset_{$image}"] = $this->boolean("reset_{$image}");
        }

        $social = $this->input('social', []);
        $merge['social'] = is_array($social) ? array_map($trim, $social) : $social;

        $links = $this->input('footer_links', []);
        $merge['footer_links'] = is_array($links)
            ? array_values(array_filter(
                array_map(fn (mixed $link) => is_array($link) ? array_map($trim, $link) : $link, $links),
                fn (mixed $link) => ! is_array($link) || filled($link['label'] ?? null) || filled($link['url'] ?? null),
            ))
            : $links;

        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'site_name' => ['required', 'string', 'max:60'],
            // ValidUtf8 is added to every text rule below, so invalid bytes never reach the JSON column.
            'site_tagline' => ['nullable', 'string', 'max:160'],
            'site_copyright' => ['required', 'string', 'max:120'],
            'social' => ['array:'.implode(',', array_keys(SiteSettings::SOCIAL_NETWORKS))],
            // Only full https:// addresses, so no javascript:, data: or plain-http links.
            'social.*' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail) {
                if (is_string($value) && $value !== '' && ! SiteSettings::isHttpsUrl($value)) {
                    $fail('Enter a full address starting with https://.');
                }
            }],
            'offcanvas_heading' => ['nullable', 'string', 'max:120'],
            'offcanvas_bio' => ['nullable', 'string', 'max:300'],
            'offcanvas_social_label' => ['required', 'string', 'max:30'],
            'offcanvas_phone_label' => ['required', 'string', 'max:30'],
            'offcanvas_email_label' => ['required', 'string', 'max:30'],
            'offcanvas_address_label' => ['required', 'string', 'max:30'],
            'footer_tagline_highlight' => ['nullable', 'string', 'max:30'],
            'footer_tagline' => ['nullable', 'string', 'max:60'],
            'footer_links_title' => ['required', 'string', 'max:40'],
            'footer_contact_title' => ['required', 'string', 'max:40'],
            'footer_links' => ['array', 'max:'.SiteSettings::MAX_FOOTER_LINKS],
            'footer_links.*' => ['array:label,url'],
            'footer_links.*.label' => ['required', 'string', 'max:30'],
            'footer_links.*.url' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail) {
                if (is_string($value) && ! SiteSettings::isAllowedLinkUrl($value)) {
                    $fail('Use a page on this site such as /blog, or a full address starting with https://.');
                }
            }],
        ];

        foreach ([...array_keys(self::TEXT_FIELDS), 'social.*', 'footer_links.*.label', 'footer_links.*.url'] as $field) {
            array_splice($rules[$field], array_search('string', $rules[$field], true) + 1, 0, [new ValidUtf8]);
        }

        foreach (self::MIN_IMAGE_SIZES as $image => [$width, $height]) {
            // SVG is not accepted: it can carry scripts.
            $rules[$image] = ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:'.UploadLimit::kilobytes(self::MAX_IMAGE_KILOBYTES), ImageUploadService::dimensionsRule($width, $height)];
            $rules["reset_{$image}"] = ['boolean'];
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
        $messages = [
            'social.array' => 'Unknown social network.',
            'footer_links.max' => 'Add at most :max quick links.',
        ];

        foreach (self::MIN_IMAGE_SIZES as $image => [$width, $height]) {
            $label = SiteSettings::IMAGES[$image]['label'];
            $messages["{$image}.mimes"] = "The {$label} must be a PNG, WebP or JPG image. SVG files are not accepted.";
            $messages["{$image}.max"] = "The {$label} may not be larger than {$limit}.";
            $messages["{$image}.uploaded"] = "The {$label} could not be uploaded. Please choose a file smaller than {$limit}.";
            $messages["{$image}.dimensions"] = ImageUploadService::dimensionsMessage("The {$label}", $width, $height);
        }

        return $messages;
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'site_name' => 'site name',
            'site_tagline' => 'site description',
            'site_copyright' => 'copyright text',
            'social.*' => 'link',
            'offcanvas_heading' => 'menu panel heading',
            'offcanvas_bio' => 'menu panel text',
            'offcanvas_social_label' => 'social links label',
            'offcanvas_phone_label' => 'phone label',
            'offcanvas_email_label' => 'email label',
            'offcanvas_address_label' => 'address label',
            'footer_tagline_highlight' => 'highlighted words',
            'footer_tagline' => 'footer tagline',
            'footer_links_title' => 'quick links title',
            'footer_contact_title' => 'contact column title',
            'footer_links.*.label' => 'link text',
            'footer_links.*.url' => 'link address',
        ] + collect(SiteSettings::IMAGES)->mapWithKeys(fn (array $image, string $key) => [$key => strtolower($image['label'])])->all();
    }
}
