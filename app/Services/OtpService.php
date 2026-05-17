<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function __construct(private readonly ExternalEmailService $emails) {}

    public function send(User $user, string $purpose): EmailOtp
    {
        $code = (string) random_int(100000, 999999);

        EmailOtp::where('email', $user->email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        $otp = EmailOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes((int) config('services.otp.expires_minutes', 10)),
        ]);

        $this->emails->otp($user->email, $code, $purpose);

        return $otp;
    }

    public function verify(string $email, string $code, string $purpose): User
    {
        $otp = EmailOtp::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp || $otp->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'The OTP has expired. Please request a new code.']);
        }

        if ($otp->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Too many OTP attempts. Please request a new code.']);
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            throw ValidationException::withMessages(['code' => 'The OTP code is invalid.']);
        }

        $otp->update(['verified_at' => now()]);
        $user = $otp->user ?? User::where('email', $email)->firstOrFail();

        if ($purpose === 'email_verification' && blank($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}
