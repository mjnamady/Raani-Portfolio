<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Privacy: inbox messages keep the sender's IP address and browser details for 90 days only.
// Runs from the scheduler: "php artisan schedule:work" in the worker container on Railway
// (scripts/railway-start.sh), or "php artisan schedule:run" every minute from cron elsewhere.
Schedule::command('messages:forget-visitor-data')->daily();

// Failed email jobs keep the message details in failed_jobs; drop them after 30 days.
Schedule::command('queue:prune-failed', ['--hours' => 720])->daily();
