<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Icon names available in the theme's bundled Font Awesome Pro stylesheet.
 */
class FontAwesomeIcons
{
    public const STYLESHEET = 'frontend/assets/css/vendor/fontawesome.css';

    /**
     * Icon styles whose font files ship with the theme.
     *
     * @var list<string>
     */
    public const STYLES = ['fa-light', 'fa-regular', 'fa-solid', 'fa-thin', 'fa-duotone', 'fa-brands'];

    /**
     * Suggestions shown in the admin icon field.
     *
     * @var list<string>
     */
    public const SUGGESTED = [
        'pen-ruler', 'bezier-curve', 'lightbulb', 'envelope', 'code', 'laptop-code', 'mobile-screen',
        'palette', 'paintbrush', 'object-group', 'layer-group', 'cube', 'chart-line', 'chart-pie',
        'magnifying-glass', 'bullhorn', 'camera', 'video', 'pen-nib', 'wand-magic-sparkles',
        'rocket', 'gears', 'server', 'database', 'cloud', 'shield-halved', 'headset', 'globe',
        'cart-shopping', 'handshake', 'graduation-cap', 'comments',
    ];

    /**
     * All icon names (without the "fa-" prefix), read once from the stylesheet and cached.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        return Cache::rememberForever('fontawesome.icon-names.v1', function (): array {
            $css = (string) @file_get_contents(public_path(self::STYLESHEET));

            preg_match_all('/\.fa-([a-z0-9]+(?:-[a-z0-9]+)*):+before/', $css, $matches);

            return array_values(array_unique($matches[1]));
        });
    }

    /**
     * Whether a full class string such as "fa-light fa-pen-ruler" is a known icon.
     */
    public static function isValid(string $class): bool
    {
        if (! preg_match('/^(fa-[a-z]+) fa-([a-z0-9]+(?:-[a-z0-9]+)*)$/', $class, $parts)) {
            return false;
        }

        return in_array($parts[1], self::STYLES, true) && in_array($parts[2], self::names(), true);
    }
}
