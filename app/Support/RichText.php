<?php

namespace App\Support;

use Mews\Purifier\Facades\Purifier;

/**
 * Sanitizes rich text from the admin editor with the strict "rich_text" allow-list in
 * config/purifier.php. Only HTML that went through clean() may be printed with {!! !!}.
 */
class RichText
{
    public const PROFILE = 'rich_text';

    /**
     * Clean the HTML, or return null when nothing visible is left.
     */
    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = trim((string) Purifier::clean($html, static::PROFILE));

        return trim(strip_tags($clean)) === '' ? null : $clean;
    }
}
