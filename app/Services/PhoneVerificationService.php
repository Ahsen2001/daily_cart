<?php

namespace App\Services;

use App\Jobs\SendPhoneOtpJob;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    private const TYPE = 'phone_verification';

    public function send(User $user): OtpVerification
    {
        if (blank($user->phone)) {
            throw ValidationException::withMessages(['phone' => 'A phone number is required for verification.']);
        }

        $code = (string) random_int(100000, 999999);

        OtpVerification::where('user_id', $user->id)
            ->where('type', self::TYPE)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        $verification = OtpVerification::create([
            'user_id' => $user->id,
            'type' => self::TYPE,
            'otp' => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('services.otp.expires_minutes', 10)),
        ]);

        SendPhoneOtpJob::dispatch($user->id, $code)->afterCommit();

        return $verification;
    }

    public function verify(User $user, string $code): User
    {
        $verification = OtpVerification::where('user_id', $user->id)
            ->where('type', self::TYPE)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $verification || $verification->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'The phone verification code has expired.']);
        }

        if ($verification->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Too many phone verification attempts. Request a new code.']);
        }

        $verification->increment('attempts');

        if (! Hash::check($code, $verification->otp)) {
            throw ValidationException::withMessages(['code' => 'The phone verification code is invalid.']);
        }

        $verification->update(['verified_at' => now()]);
        $user->markPhoneAsVerified();

        return $user->refresh();
    }
}
