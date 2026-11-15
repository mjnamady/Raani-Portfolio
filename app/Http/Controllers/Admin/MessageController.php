<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The messages inbox (ADM-3, A-6): a list with status and source filters, a reading pane, and
 * read / replied / delete actions. Opening a message marks it as read.
 */
class MessageController extends Controller
{
    public const PER_PAGE = 20;

    public const STATUSES = ['all', 'unread', 'replied'];

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Message::class);

        return view('admin.messages.index', $this->inbox($request) + ['selected' => null]);
    } // End method

    public function show(Request $request, Message $message): View
    {
        Gate::authorize('view', $message);

        if ($message->isUnread()) {
            $message->forceFill(['read_at' => now()])->save();
        }

        return view('admin.messages.index', $this->inbox($request) + ['selected' => $message->load(['project', 'replies'])]);
    } // End method

    public function markRead(Request $request, Message $message): RedirectResponse
    {
        Gate::authorize('update', $message);

        $message->forceFill(['read_at' => $message->read_at ?? now()])->save();

        return $this->backToMessage($request, $message, 'Marked as read.');
    } // End method

    public function markUnread(Request $request, Message $message): RedirectResponse
    {
        Gate::authorize('update', $message);

        $message->forceFill(['read_at' => null])->save();

        // Back to the list: reopening the message would mark it as read again.
        return redirect()->route('admin.messages.index', $this->filters($request))
            ->with('status', 'Marked as unread.');
    } // End method

    public function markReplied(Request $request, Message $message): RedirectResponse
    {
        Gate::authorize('update', $message);

        $message->forceFill(['replied_at' => $message->replied_at ?? now(), 'read_at' => $message->read_at ?? now()])->save();

        return $this->backToMessage($request, $message, 'Marked as replied.');
    } // End method

    public function markUnreplied(Request $request, Message $message): RedirectResponse
    {
        Gate::authorize('update', $message);

        $message->forceFill(['replied_at' => null])->save();

        return $this->backToMessage($request, $message, 'No longer marked as replied.');
    } // End method

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        Gate::authorize('delete', $message);

        $message->delete();

        return redirect()->route('admin.messages.index', $this->filters($request))
            ->with('status', 'The message has been deleted.');
    } // End method

    /**
     * The list, tab counts and current filters shared by the index and the reading view.
     *
     * @return array{waitingEmails: int, messages: LengthAwarePaginator<int, Message>, counts: array<string, int>, filters: array{status?: string, source?: string}, status: string, source: ?string}
     */
    protected function inbox(Request $request): array
    {
        $filters = $this->filters($request);
        $status = $filters['status'] ?? 'all';
        $source = $filters['source'] ?? null;

        $messages = Message::query()
            ->when($source, fn ($query) => $query->fromSource($source))
            ->when($status === 'unread', fn ($query) => $query->unread())
            ->when($status === 'replied', fn ($query) => $query->whereNotNull('replied_at'))
            ->latest()
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $base = Message::query()->when($source, fn ($query) => $query->fromSource($source));

        return [
            'waitingEmails' => self::waitingEmails(),
            'messages' => $messages,
            'counts' => [
                'all' => (clone $base)->count(),
                'unread' => (clone $base)->unread()->count(),
                'replied' => (clone $base)->whereNotNull('replied_at')->count(),
            ],
            'filters' => $filters,
            'status' => $status,
            'source' => $source,
        ];
    }

    /**
     * The status and source filters from the query string or the action forms, allow-listed.
     *
     * @return array{status?: string, source?: string}
     */
    protected function filters(Request $request): array
    {
        $status = $request->input('status');
        $source = $request->input('source');

        return array_filter([
            'status' => is_string($status) && in_array($status, self::STATUSES, true) && $status !== 'all' ? $status : null,
            'source' => is_string($source) && array_key_exists($source, Message::SOURCES) ? $source : null,
        ]);
    }

    /**
     * Queued jobs that were due more than two minutes ago and nobody has picked up: a sign the queue
     * worker is not running, so emails are not being sent. (Jobs waiting for a retry pause are not due yet.)
     */
    public static function waitingEmails(): int
    {
        if (config('queue.default') !== 'database') {
            return 0;
        }

        return DB::table((string) config('queue.connections.database.table', 'jobs'))
            ->whereNull('reserved_at')
            ->where('available_at', '<', now()->subMinutes(2)->getTimestamp())
            ->count();
    }

    protected function backToMessage(Request $request, Message $message, string $status): RedirectResponse
    {
        return redirect()->route('admin.messages.show', ['message' => $message] + $this->filters($request))
            ->with('status', $status);
    }
}
