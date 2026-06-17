<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\StagingBasicAuth::class,
        ]);
        $middleware->alias([
            'bridge.hmac' => \App\Http\Middleware\VerifyBridgeHmac::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/webhook/didit',
            '/ajax/track-form-start',
            '/ajax/track-utm-visit',
            '/ajax/validate-code',
            '/ajax/abandoned-contact/*',
            '/ajax/abandoned-dismiss/*',
            '/api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // PCI-DSS: never flash card data into the session on the framework's
        // automatic redirect-with-input after a ValidationException (Vector A).
        // Manual ->withInput() bouncebacks are filtered separately via
        // UsersController::cardSafeInput() (Vector B). Shared field list.
        $exceptions->dontFlash(config('payment.card_fields_to_exclude'));
    })->create();
