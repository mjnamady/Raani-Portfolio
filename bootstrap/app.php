<?php

use App\Http\Middleware\EnsureAdmin;
use App\Support\FlashableInput;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);

        // Signed-in users who open a guest page (e.g. /login) go to the admin panel, not the public home page.
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Before Laravel flashes the failed input back to the form, drop arrays sent where text is
        // expected, so the form page does not crash on old(). Returning null keeps the default response.
        $exceptions->render(function (ValidationException $exception, Request $request) {
            $request->replace(FlashableInput::from($request->input(), $exception->validator));

            return null;
        });
    })->create();
