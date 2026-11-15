<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Minimum duration of a login attempt, in microseconds.
     */
    protected const LOGIN_TIMEBOX_MICROSECONDS = 400_000;

    /**
     * The resolved throttle key, cached for the lifetime of the request.
     */
    protected ?string $throttleKey = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get the custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login' => 'email or username',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            $this->loginField() => $this->loginValue(),
            'password' => $this->string('password')->value(),
            'is_admin' => true,
        ];

        // Failed attempts always take the full timebox, so response time does not reveal which accounts exist.
        $authenticated = (new Timebox)->call(function (Timebox $timebox) use ($credentials) {
            if (Auth::attempt($credentials, $this->boolean('remember'))) {
                $timebox->returnEarly();

                return true;
            }

            return false;
        }, self::LOGIN_TIMEBOX_MICROSECONDS);

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Get the trimmed email or username entered by the user.
     */
    protected function loginValue(): string
    {
        return $this->string('login')->trim()->value();
    }

    /**
     * Determine whether the login value is an email address or a username.
     */
    protected function loginField(): string
    {
        return filter_var($this->loginValue(), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * Keyed on the matching account when one exists, so the email and the
     * username of the same account share a single bucket.
     */
    public function throttleKey(): string
    {
        if ($this->throttleKey !== null) {
            return $this->throttleKey;
        }

        $userId = User::where($this->loginField(), $this->loginValue())->value('id');

        $subject = $userId !== null
            ? 'user:'.$userId
            : 'input:'.Str::transliterate(Str::lower($this->loginValue()));

        return $this->throttleKey = $subject.'|'.$this->ip();
    }
}
