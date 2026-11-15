<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Site-wide settings (M3: GEN-1, GEN-3, GEN-5, GEN-6, GEN-7 defaults) stored in the "settings" table:
 * identity ("site"), social links ("social"), the off-canvas menu panel ("offcanvas") and the
 * footer ("footer"). Contact details live in ContactDetails ("contact").
 */
class SiteSettings
{
    /**
     * Social networks offered in the admin, in display order, with their theme icon (GEN-3).
     * The theme's Font Awesome has no X logo, so X keeps the theme's bird icon.
     *
     * @var array<string, array{label: string, icon: string}>
     */
    public const SOCIAL_NETWORKS = [
        'instagram' => ['label' => 'Instagram', 'icon' => 'fa-instagram'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fa-linkedin-in'],
        'x' => ['label' => 'X (Twitter)', 'icon' => 'fa-twitter'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'fa-facebook-f'],
        'github' => ['label' => 'GitHub', 'icon' => 'fa-github'],
        'dribbble' => ['label' => 'Dribbble', 'icon' => 'fa-dribbble'],
        'behance' => ['label' => 'Behance', 'icon' => 'fa-behance'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'fa-youtube'],
    ];

    /**
     * Uploadable images: the setting key, the theme image shown until one is uploaded, and how
     * uploads are resized ("fit" keeps the whole image within the box, "crop" fills it exactly).
     *
     * @var array<string, array{key: string, default: string, mode: string, width: int, height: int, label: string}>
     */
    public const IMAGES = [
        'logo' => ['key' => 'site.logo', 'default' => 'frontend/assets/images/logo/white-logo-reeni-2.png', 'mode' => 'fit', 'width' => 480, 'height' => 160, 'label' => 'Logo'],
        'logo_light' => ['key' => 'site.logo_light', 'default' => 'frontend/assets/images/logo/logo-white.png', 'mode' => 'fit', 'width' => 480, 'height' => 160, 'label' => 'Logo for light mode'],
        'favicon' => ['key' => 'site.favicon', 'default' => 'frontend/assets/images/favicon.svg', 'mode' => 'crop', 'width' => 64, 'height' => 64, 'label' => 'Browser tab icon'],
        'offcanvas_image' => ['key' => 'offcanvas.image', 'default' => 'frontend/assets/images/logo/man.png', 'mode' => 'fit', 'width' => 400, 'height' => 400, 'label' => 'Menu panel photo'],
    ];

    /**
     * Most quick links in the footer (GEN-5).
     */
    public const MAX_FOOTER_LINKS = 6;

    /**
     * Every text setting and its value until it is saved, keyed by setting key.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'site.name' => (string) config('app.name'),
            'site.tagline' => 'Personal portfolio: projects, services and writing.',
            'site.copyright' => '© {year} {site}. All rights reserved.',
            'offcanvas.heading' => 'Freelancer delivering thoughtful design and development.',
            'offcanvas.bio' => '',
            'offcanvas.social_label' => 'Find with me',
            'offcanvas.phone_label' => 'Call Now',
            'offcanvas.email_label' => 'Mail Me',
            'offcanvas.address_label' => 'My Address',
            'footer.tagline_highlight' => 'Get Ready',
            'footer.tagline' => 'To Create Great',
            'footer.links_title' => 'Quick Links',
            'footer.links' => [
                ['label' => 'About Me', 'url' => '/about'],
                ['label' => 'Projects', 'url' => '/projects'],
                ['label' => 'Blog', 'url' => '/blog'],
                ['label' => 'Contact', 'url' => '/contact'],
            ],
            'footer.contact_title' => 'Contact',
        ] + collect(self::SOCIAL_NETWORKS)->mapWithKeys(fn (array $network, string $key) => ["social.{$key}" => ''])->all();
    }

    /**
     * One group's settings with defaults for anything not saved yet, keyed by the part after the
     * group ("name" for "site.name").
     *
     * @return array<string, mixed>
     */
    public static function group(string $group): array
    {
        $saved = Setting::group($group);
        $settings = [];

        // Only known settings, and only stored values of the default's type: a hand-edited row can
        // neither break the pages nor slip in a key that overrides a checked value.
        foreach (self::defaults() as $key => $default) {
            if (! str_starts_with($key, $group.'.')) {
                continue;
            }

            $name = substr($key, strlen($group) + 1);
            $value = $saved[$name] ?? $default;
            $settings[$name] = gettype($value) === gettype($default) ? $value : $default;
        }

        return $settings;
    }

    /**
     * Public URL of an uploaded image, or its theme image when none is uploaded.
     */
    public static function imageUrl(string $image): string
    {
        $path = Setting::get(self::IMAGES[$image]['key']);

        return self::isOwnedImagePath($path) ? Storage::disk('public')->url($path) : asset(self::IMAGES[$image]['default']);
    }

    /**
     * Whether an image has been uploaded (false while the theme image is used).
     */
    public static function hasUploadedImage(string $image): bool
    {
        return self::isOwnedImagePath(Setting::get(self::IMAGES[$image]['key']));
    }

    /**
     * A path this feature stored itself ("settings/<uuid>.webp"). Only these are shown or ever
     * deleted, so a hand-edited value can not point at another feature's file.
     */
    public static function isOwnedImagePath(mixed $path): bool
    {
        return is_string($path) && preg_match('#^settings/[0-9a-f-]{36}\.webp$#', $path) === 1;
    }

    /**
     * The copyright line with {year} and {site} filled in on the server.
     */
    public static function copyright(): string
    {
        $site = self::group('site');

        return strtr((string) $site['copyright'], ['{year}' => now()->year, '{site}' => (string) $site['name']]);
    }

    /**
     * Social links that are set, in display order. Only https links are shown, whatever is stored.
     *
     * @return list<array{key: string, label: string, icon: string, url: string}>
     */
    public static function socialLinks(): array
    {
        $saved = self::group('social');
        $links = [];

        foreach (self::SOCIAL_NETWORKS as $key => $network) {
            $url = $saved[$key] ?? '';

            if (is_string($url) && self::isHttpsUrl($url)) {
                $links[] = ['key' => $key, 'label' => $network['label'], 'icon' => $network['icon'], 'url' => $url];
            }
        }

        return $links;
    }

    /**
     * Footer quick links with usable URLs: site paths become full URLs, https links stay as they are.
     * "external" is true when the link leaves this site's host (it then opens in a new tab).
     *
     * @return list<array{label: string, url: string, external: bool}>
     */
    public static function footerLinks(): array
    {
        $links = self::group('footer')['links'];
        $siteHost = strtolower((string) parse_url(url('/'), PHP_URL_HOST));

        return collect($links)
            ->filter(fn (mixed $link) => is_array($link) && is_string($link['label'] ?? null) && is_string($link['url'] ?? null) && self::isAllowedLinkUrl($link['url']))
            ->map(fn (array $link) => str_starts_with($link['url'], '/')
                ? ['label' => $link['label'], 'url' => url($link['url']), 'external' => false]
                : ['label' => $link['label'], 'url' => $link['url'], 'external' => strtolower((string) parse_url($link['url'], PHP_URL_HOST)) !== $siteHost])
            ->values()
            ->all();
    }

    /**
     * A full https:// address (no other scheme, no spaces).
     */
    public static function isHttpsUrl(string $url): bool
    {
        return str_starts_with(strtolower($url), 'https://')
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && ! preg_match('/\s/', $url);
    }

    /**
     * A link a footer quick link may point to: a path on this site ("/blog", not "//other.site")
     * or a full https:// address.
     */
    public static function isAllowedLinkUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//') && ! str_contains($url, '\\') && preg_match('#^/[A-Za-z0-9\-._~/?&=%\#]*$#', $url) === 1;
        }

        return self::isHttpsUrl($url);
    }
}
