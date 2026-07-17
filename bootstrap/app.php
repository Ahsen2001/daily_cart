<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsurePhoneIsVerified;
use App\Http\Middleware\EnsureRiderApproved;
use App\Http\Middleware\EnsureVendorApproved;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'payments/payhere/notify',
            'logout',
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'vendor.approved' => EnsureVendorApproved::class,
            'rider.approved' => EnsureRiderApproved::class,
            'phone.verified' => EnsurePhoneIsVerified::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
