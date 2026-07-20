<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PasswordRecoveryController extends Controller
{
    public function forgot(Request $request, OtpService $otps): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user) {
            $otps->send($user, 'password_reset');
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account exists, a password reset code has been sent.',
        ]);
    }

    public function reset(Request $request, OtpService $otps): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $otps->verify($validated['email'], $validated['code'], 'password_reset');
        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Sign in with your new password.',
        ]);
    }
}
