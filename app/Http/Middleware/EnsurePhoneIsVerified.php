<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasVerifiedPhone()) {
            return new JsonResponse([
                'message' => 'Your phone number must be verified before using this feature.',
            ], 403);
        }

        return $next($request);
    }
}
