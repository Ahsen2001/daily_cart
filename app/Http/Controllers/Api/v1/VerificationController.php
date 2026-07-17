<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\OtpService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function sendEmail(Request $request, OtpService $otps): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $otps->send($request->user(), 'email_verification');
        }

        return response()->json(['message' => 'If verification is required, an email code has been sent.']);
    }

    public function verifyEmail(Request $request, OtpService $otps): JsonResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $otps->verify($request->user()->email, $validated['code'], 'email_verification');

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => new UserResource($user),
        ]);
    }

    public function sendPhone(Request $request, PhoneVerificationService $phones): JsonResponse
    {
        if (! $request->user()->hasVerifiedPhone()) {
            $phones->send($request->user());
        }

        return response()->json(['message' => 'If verification is required, a phone code has been sent.']);
    }

    public function verifyPhone(Request $request, PhoneVerificationService $phones): JsonResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $phones->verify($request->user(), $validated['code']);

        return response()->json([
            'message' => 'Phone verified successfully.',
            'user' => new UserResource($user),
        ]);
    }
}
