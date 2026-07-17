<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ExternalPushService;
use App\Services\ExternalSmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationChannelJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly string $channel,
        public readonly string $title,
        public readonly string $message,
    ) {
        $this->afterCommit();
    }

    public function handle(ExternalSmsService $sms, ExternalPushService $push): void
    {
        $user = User::findOrFail($this->userId);

        match ($this->channel) {
            'sms' => $sms->send($user->phone, $this->message),
            'push' => $push->send($user, $this->title, $this->message),
            'whatsapp' => Log::notice('WhatsApp notification provider is not configured.', [
                'user_id' => $user->id,
            ]),
            default => null,
        };
    }
}
