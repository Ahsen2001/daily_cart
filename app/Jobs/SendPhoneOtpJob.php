<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ExternalSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPhoneOtpJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly string $code,
    ) {
        $this->afterCommit();
    }

    public function handle(ExternalSmsService $sms): void
    {
        $user = User::findOrFail($this->userId);

        $sms->send(
            $user->phone,
            'Your DailyCart phone verification code is '.$this->code.'. It expires in '.config('services.otp.expires_minutes', 10).' minutes.'
        );
    }
}
