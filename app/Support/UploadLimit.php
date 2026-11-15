<?php

namespace App\Support;

/**
 * The largest upload that will actually reach the app: the smaller of the app's own
 * limit and PHP's upload_max_filesize / post_max_size. Files above PHP's limits are
 * dropped before Laravel sees them, so validation and the admin hints use this value.
 */
class UploadLimit
{
    /**
     * Effective limit in kilobytes (for the "max" validation rule).
     */
    public static function kilobytes(int $appLimitKilobytes): int
    {
        $phpLimits = array_filter([
            static::iniBytes('upload_max_filesize'),
            static::iniBytes('post_max_size'),
        ]);

        $phpLimitKilobytes = $phpLimits ? intdiv(min($phpLimits), 1024) : PHP_INT_MAX;

        return min($appLimitKilobytes, $phpLimitKilobytes);
    }

    /**
     * Effective limit in bytes (for the client-side check).
     */
    public static function bytes(int $appLimitKilobytes): int
    {
        return static::kilobytes($appLimitKilobytes) * 1024;
    }

    /**
     * Effective limit for people, e.g. "2 MB" or "500 KB".
     */
    public static function label(int $appLimitKilobytes): string
    {
        $kilobytes = static::kilobytes($appLimitKilobytes);

        return $kilobytes >= 1024
            ? rtrim(rtrim(number_format($kilobytes / 1024, 1), '0'), '.').' MB'
            : $kilobytes.' KB';
    }

    /**
     * A php.ini size setting in bytes, or null when it is unlimited or unset.
     */
    protected static function iniBytes(string $setting): ?int
    {
        $value = trim((string) ini_get($setting));

        if ($value === '' || $value === '0' || $value === '-1') {
            return null;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
