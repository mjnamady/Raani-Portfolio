<?php

namespace App\Support;

use Illuminate\Contracts\Validation\Validator;

/**
 * The part of a failed request that is safe to flash back to the form as old input.
 *
 * Validation rejects an array sent where text is expected, but Laravel would still flash it,
 * and the form page then crashes on {{ old('name') }} (htmlspecialchars() needs a string).
 * Top-level arrays are only kept for fields whose rules expect one (e.g. "tags", "features").
 */
class FlashableInput
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function from(array $input, ?Validator $validator = null): array
    {
        $rules = $validator?->getRules() ?? [];

        return array_filter($input, function (mixed $value, string|int $key) use ($rules) {
            if (! is_array($value)) {
                return is_scalar($value) || $value === null;
            }

            return in_array('array', $rules[$key] ?? [], true)
                || collect(array_keys($rules))->contains(fn (string $rule) => str_starts_with($rule, $key.'.'));
        }, ARRAY_FILTER_USE_BOTH);
    }
}
