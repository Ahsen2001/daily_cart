<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRiderApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $rider = $request->user()?->rider;

        if (! $rider || $rider->verification_status !== 'verified') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your rider account is not approved.',
                ], 403);
            }

            return redirect()->route('rider.pending');
        }

        return $next($request);
    }
}
