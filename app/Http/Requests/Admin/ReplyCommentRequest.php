<?php

namespace App\Http\Requests\Admin;

use App\Models\Comment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The owner's reply to a comment (A-7).
 */
class ReplyCommentRequest extends FormRequest
{
    /**
     * Reply errors go to their own bag; the "reply_to" old input tells the page which reply box to reopen.
     *
     * @var string
     */
    protected $errorBag = 'reply';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('comment'));
    }

    /**
     * Trim the reply. Non-string input is passed through so the "string" rule rejects it.
     */
    protected function prepareForValidation(): void
    {
        $body = $this->input('body');

        $this->merge([
            'body' => is_string($body) ? trim(str_replace("\r\n", "\n", $body)) : $body,
            'reply_to' => $this->route('comment')?->getKey(),
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
            'body' => ['required', 'string', 'min:2', 'max:'.Comment::MAX_BODY_LENGTH],
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
