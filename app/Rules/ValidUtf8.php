<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Text must be valid UTF-8: invalid bytes would make MySQL (or the JSON cast) refuse the row,
 * turning a typo into a 500 instead of a validation error.
 */
class ValidUtf8 implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
            $fail('The :attribute contains characters that can not be saved.');
        }
    }
}
