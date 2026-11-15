<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Post;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A visitor's comment on a published post (BLG-9, V-5). The honeypot is checked by the controller.
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Name of the hidden field bots tend to fill in (CON-4). Deliberately not a name browsers or
     * password managers autofill ("website", "url"...): an autofilled honeypot would silently
     * discard a real visitor's comment.
     */
    public const HONEYPOT = 'hp_leave_empty';

    /**
     * Validation errors go to their own bag, so they only show under the comment form.
     *
     * @var string
     */
    protected $errorBag = 'comment';

    /**
     * Anyone may comment on a live post; spam is handled by the honeypot, the rate limit and moderation.
     * Drafts, future posts and unknown slugs are 404, the same as the post page.
     */
    public function authorize(): bool
    {
        abort_unless($this->commentedPost()?->isLive(), 404);

        return true;
    }

    /**
     * The post from the route ({post:slug}).
     */
    public function commentedPost(): ?Post
    {
        $post = $this->route('post');

        return $post instanceof Post ? $post : null;
    }

    /**
     * On errors, go back to the form on the post page (with the errors and input), not the top of the page.
     */
    protected function getRedirectUrl(): string
    {
        return route('blog.show', $this->commentedPost()->slug).'#comment-form';
    }

    /**
     * Trim the text fields and strip invisible control and formatting characters (zero-width,
     * bidi overrides), keeping newlines in the message and the joiner used by emoji.
     * Non-string and invalid UTF-8 input is passed through so the rules reject it.
     */
    protected function prepareForValidation(): void
    {
        $clean = function (mixed $value, bool $multiline = false) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8')) {
                return $value;
            }

            $value = str_replace(["\r\n", "\t"], ["\n", ' '], $value);
            $value = preg_replace($multiline ? '/(?![\n\x{200D}])[\p{Cc}\p{Cf}]/u' : '/(?!\x{200D})[\p{Cc}\p{Cf}]/u', '', $value);

            return trim($value);
        };

        $this->merge([
            'author_name' => $clean($this->input('author_name')),
            'author_email' => $clean($this->input('author_email')),
            'body' => $clean($this->input('body'), multiline: true),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Invalid UTF-8 would make MySQL refuse the row (a 500) instead of a validation error.
        $validUtf8 = function (string $attribute, mixed $value, Closure $fail) {
            if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
                $fail('The :attribute contains characters that can not be saved.');
            }
        };

        return [
            'author_name' => ['required', 'string', $validUtf8, 'max:80'],
            // ? & # % are valid in an address but would add parameters to the owner's "mailto:" reply link.
            'author_email' => ['required', 'string', $validUtf8, 'email', 'not_regex:/[?&#%]/', 'max:255'],
            'body' => ['required', 'string', $validUtf8, 'min:2', 'max:'.Comment::MAX_BODY_LENGTH],
            'parent_id' => [
                'nullable', 'integer',
                // Replies go one level deep, to an approved comment on this same post.
                function (string $attribute, mixed $value, Closure $fail) {
                    $isValidParent = Comment::query()
                        ->whereKey($value)
                        ->where('post_id', $this->commentedPost()?->id)
                        ->whereNull('parent_id')
                        ->approved()
                        ->exists();

                    if (! $isValidParent) {
                        $fail('That comment can not be replied to. Please post a new comment instead.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['author_name' => 'name', 'author_email' => 'email', 'body' => 'message'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['author_email.not_regex' => 'Please enter an email address without ? & # or % characters.'];
    }
}
