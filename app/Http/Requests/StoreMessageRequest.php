<?php

namespace App\Http\Requests;

use App\Models\Message;
use App\Models\Project;
use App\Rules\ValidUtf8;
use App\Support\InvisibleCharacters;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A visitor's message from a contact form, a project page or the chat popup (CON-1, CON-2, V-4).
 * The honeypot is checked by the controller; spam is also limited by the "messages" rate limiter.
 */
class StoreMessageRequest extends FormRequest
{
    /**
     * Name of the hidden field bots tend to fill in (CON-4). Deliberately not a name browsers or
     * password managers autofill ("website", "url", "company"...): an autofilled honeypot would
     * silently discard a real visitor's message.
     */
    public const HONEYPOT = 'hp_leave_empty';

    /**
     * Anyone may send a message.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Clean the text and send errors to a bag per form ("message_contact", "message_chat" ...), so
     * a page with two forms shows them under the right one. Non-string input is passed through
     * so the rules reject it.
     */
    protected function prepareForValidation(): void
    {
        $source = $this->input('source');
        $this->errorBag = 'message_'.(is_string($source) && array_key_exists($source, Message::SOURCES) ? $source : Message::SOURCE_CONTACT);

        $clean = fn (mixed $value, bool $multiline = false) => is_string($value) ? InvisibleCharacters::strip($value, $multiline) : $value;

        $this->merge([
            'name' => $clean($this->input('name')),
            'email' => $clean($this->input('email')),
            'phone' => $clean($this->input('phone')),
            'subject' => $clean($this->input('subject')),
            'body' => $clean($this->input('body'), multiline: true),
        ]);
    }

    /**
     * Back to the form the visitor used, on this site only.
     */
    protected function getRedirectUrl(): string
    {
        return self::backToForm($this->input('source'));
    }

    /**
     * The page the form was sent from (or the home page when that is not this site), at the form.
     */
    public static function backToForm(mixed $source): string
    {
        $previous = url()->previous();
        $page = parse_url($previous, PHP_URL_HOST) === parse_url(url('/'), PHP_URL_HOST) ? strtok($previous, '#') : url('/');

        return $page.($source === Message::SOURCE_CHAT ? '#chat-form' : '#contact-form');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $utf8 = new ValidUtf8;

        return [
            'source' => ['required', 'string', Rule::in(array_keys(Message::SOURCES))],
            'name' => ['required', 'string', $utf8, 'max:80'],
            // ? & # % are valid in an address but would add parameters to the owner's "Reply by email" link.
            'email' => ['required', 'string', $utf8, 'email', 'not_regex:/[?&#%]/', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]+$/', 'regex:/\d/'],
            'subject' => ['nullable', 'string', $utf8, 'max:150'],
            'body' => ['required', 'string', $utf8, 'min:10', 'max:'.Message::MAX_BODY_LENGTH],
            'project_id' => [
                'nullable', 'required_if:source,'.Message::SOURCE_PROJECT, 'integer',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! Project::query()->published()->whereKey($value)->exists()) {
                        $fail('That project can not be found.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.not_regex' => 'Please enter an email address without ? & # or % characters.',
            'phone.regex' => 'Phone numbers may only contain digits, spaces and + ( ) - . characters.',
            'body.min' => 'Please write a little more (at least :min characters).',
            'project_id.required_if' => 'That project can not be found.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['body' => 'message', 'project_id' => 'project'];
    }
}
