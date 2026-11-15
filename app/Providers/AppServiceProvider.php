<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Each reset route gets its own buckets: per IP, and per target email across IPs.
        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(5)->by($request->path().'|ip|'.$request->ip()),
            Limit::perHour(10)->by($request->path().'|email|'.Str::lower(trim((string) $request->input('email')))),
        ]);

        // Routes that check the current password or change account credentials: 6 per minute per user and route.
        RateLimiter::for('account-security', fn (Request $request) => Limit::perMinute(6)->by(
            $request->route()?->getName().'|'.($request->user()?->getAuthIdentifier() ?? $request->ip())
        ));
    }
}
