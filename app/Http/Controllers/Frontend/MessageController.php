<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Mail\NewMessageMail;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Visitor messages (CON-1, CON-2, CON-3): saved to the admin inbox and emailed to the owner.
 * Answers with JSON when sent from JavaScript, and with a redirect back to the form otherwise.
 */
class MessageController extends Controller
{
    public const THANK_YOU = 'Thanks, your message has been sent. I will get back to you soon.';

    public function store(StoreMessageRequest $request): JsonResponse|RedirectResponse
    {
        $source = $request->validated('source');

        // Bots that fill the hidden field get the normal answer, but nothing is saved (CON-4).
        // Logged (form only, no personal data) so a discarded message never goes unnoticed.
        if (filled($request->input(StoreMessageRequest::HONEYPOT))) {
            Log::info('Message discarded by the honeypot.', ['form' => $source]);

            return $this->thankYou($request, $source);
        }

        $message = new Message($request->safe()->only(['name', 'email', 'phone', 'subject', 'body']));
        $message->source = $source;
        $message->project_id = $source === Message::SOURCE_PROJECT ? $request->validated('project_id') : null;
        $message->ip_address = $request->ip();
        // mb_scrub(): invalid UTF-8 in the header would make MySQL refuse the row.
        $message->user_agent = Str::limit(mb_scrub((string) $request->userAgent(), 'UTF-8'), 250, '');
        $message->save();

        // The message is in the inbox either way; a mail problem must never fail the visitor's request.
        try {
            $owners = User::query()->where('is_admin', true)->pluck('email')->filter()->all();

            if ($owners !== []) {
                Mail::to($owners)->queue(new NewMessageMail($message));
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $this->thankYou($request, $source);
    } // End method

    /**
     * The success answer: JSON for the JavaScript form, otherwise back to the form with a flash message.
     */
    protected function thankYou(StoreMessageRequest $request, string $source): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => self::THANK_YOU]);
        }

        return redirect()->to(StoreMessageRequest::backToForm($source))->with("message_status_{$source}", self::THANK_YOU);
    }
}
