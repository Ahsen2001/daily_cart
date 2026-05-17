<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'payments/payhere/notify',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'vendor.approved' => \App\Http\Middleware\EnsureVendorApproved::class,
            'rider.approved' => \App\Http\Middleware\EnsureRiderApproved::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
