<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Privacy: clears the visitor IP address and browser details from inbox messages older than
 * Message::KEEP_VISITOR_DATA_DAYS. The messages themselves stay until the owner deletes them.
 * Scheduled daily in routes/console.php.
 */
#[Signature('messages:forget-visitor-data')]
#[Description('Clear the IP address and browser details from old inbox messages')]
class ForgetMessageVisitorData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cleared = Message::query()
            ->where('created_at', '<', now()->subDays(Message::KEEP_VISITOR_DATA_DAYS))
            ->where(fn ($query) => $query->whereNotNull('ip_address')->orWhereNotNull('user_agent'))
            ->update(['ip_address' => null, 'user_agent' => null]);

        $this->info("Cleared visitor details from {$cleared} ".str('message')->plural($cleared).'.');

        return self::SUCCESS;
    }
}
