<?php

namespace App\Support;

/**
 * Cleans one-line text from forms: whitespace runs become one space, and invisible control and
 * formatting characters (zero-width, bidi overrides that could spoof a name) are removed. The
 * zero-width joiner stays, since emoji sequences need it.
 */
class InvisibleCharacters
{
    /**
     * The cleaned text, or the input unchanged when it is not valid UTF-8 (so ValidUtf8 reports it).
     */
    public static function strip(string $value, bool $multiline = false): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        if ($multiline) {
            $value = str_replace(["\r\n", "\r", "\t"], ["\n", "\n", ' '], $value);
            $value = preg_replace('/(?![\n\x{200D}])[\p{Cc}\p{Cf}]/u', '', $value);
            $value = preg_replace("/\n{3,}/", "\n\n", $value);
        } else {
            $value = preg_replace('/\s+/u', ' ', $value);
            $value = preg_replace('/(?!\x{200D})[\p{Cc}\p{Cf}]/u', '', $value);
        }

        return trim($value);
    }
}
