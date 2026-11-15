<?php

namespace App\Http\Requests\Admin;

use App\Models\MessageReply;
use App\Rules\ValidUtf8;
use App\Support\InvisibleCharacters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The owner's reply to an inbox message (ADM-3). Only the subject and text come from the form:
 * the recipient is always the message's own stored address.
 */
class StoreMessageReplyRequest extends FormRequest
{
    /**
     * Errors go to their own bag, so they show in the reply box.
     *
     * @var string
     */
    protected $errorBag = 'reply';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('message'));
    }

    /**
     * The subject becomes one clean line (no line breaks can reach the mail headers); the text keeps
     * its line breaks. Non-string input is passed through so the rules reject it.
     */
    protected function prepareForValidation(): void
    {
        $subject = $this->input('subject');
        $body = $this->input('body');

        $this->merge([
            'subject' => is_string($subject) ? InvisibleCharacters::strip($subject) : $subject,
            'body' => is_string($body) ? InvisibleCharacters::strip($body, multiline: true) : $body,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', new ValidUtf8, 'max:150'],
            'body' => ['required', 'string', new ValidUtf8, 'min:2', 'max:'.MessageReply::MAX_BODY_LENGTH],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['body' => 'reply'];
    }
}
