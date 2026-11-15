<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Privacy: inbox messages keep the sender's IP address and browser details for 90 days only.
// Needs "php artisan schedule:run" every minute from cron on the server.
Schedule::command('messages:forget-visitor-data')->daily();
