<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use App\Rules\ValidUtf8;
use App\Support\ContactDetails;
use App\Support\InvisibleCharacters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The Contact page: address, email and phone cards (GEN-2) and the shared "Get in touch" text.
 */
class UpdateContactPageRequest extends FormRequest
{
    /**
     * The list fields, each with up to ContactDetails::MAX_LINES lines.
     */
    public const LISTS = ['address_lines', 'emails', 'phones'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', Setting::class);
    }

    /**
     * Trim the text fields and drop empty lines. Non-string input is passed through so the
     * rules reject it.
     */
    protected function prepareForValidation(): void
    {
        $trim = fn (mixed $value) => is_string($value) ? InvisibleCharacters::strip($value) : $value;
        $merge = [];

        foreach (['address_title', 'email_title', 'phone_title', 'touch_eyebrow', 'touch_title', 'touch_text'] as $field) {
            $merge[$field] = $trim($this->input($field));
        }

        foreach (self::LISTS as $field) {
            $lines = $this->input($field, []);

            $merge[$field] = is_array($lines)
                ? array_values(array_filter(array_map($trim, $lines), fn (mixed $line) => $line !== '' && $line !== null))
                : $lines;
        }

        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxLines = 'max:'.ContactDetails::MAX_LINES;
        $utf8 = new ValidUtf8;

        return [
            'address_title' => ['required', 'string', $utf8, 'max:40'],
            'address_lines' => ['array', $maxLines],
            'address_lines.*' => ['string', $utf8, 'max:120'],
            'email_title' => ['required', 'string', $utf8, 'max:40'],
            'emails' => ['array', $maxLines],
            'emails.*' => self::emailRules(),
            'phone_title' => ['required', 'string', $utf8, 'max:40'],
            'phones' => ['array', $maxLines],
            'phones.*' => ['string', 'max:40', 'regex:/^[0-9+().\-\s]+$/', 'regex:/\d/'],
            'touch_eyebrow' => ['nullable', 'string', $utf8, 'max:60'],
            'touch_title' => ['required', 'string', $utf8, 'max:120'],
            'touch_text' => ['nullable', 'string', $utf8, 'max:255'],
        ];
    }

    /**
     * Rules for one public email address; the seeder checks copied banner emails with them too.
     *
     * @return list<mixed>
     */
    public static function emailRules(): array
    {
        // ? & # % are valid in an address but would add parameters to the "mailto:" link.
        return ['string', new ValidUtf8, 'email', 'not_regex:/[?&#%]/', 'max:150', 'distinct:ignore_case'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_lines.max' => 'Add at most :max address lines.',
            'emails.max' => 'Add at most :max email addresses.',
            'phones.max' => 'Add at most :max phone numbers.',
            'emails.*.not_regex' => 'Email addresses can not contain ? & # or % characters.',
            'emails.*.distinct' => 'The same email address is listed twice.',
            'phones.*.regex' => 'Phone numbers may only contain digits, spaces and + ( ) - . characters.',
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
            'address_title' => 'address card title',
            'address_lines' => 'address lines',
            'address_lines.*' => 'address line',
            'email_title' => 'email card title',
            'emails.*' => 'email address',
            'phone_title' => 'phone card title',
            'phones.*' => 'phone number',
            'touch_eyebrow' => 'small heading',
            'touch_title' => 'title',
            'touch_text' => 'text',
        ];
    }
}
