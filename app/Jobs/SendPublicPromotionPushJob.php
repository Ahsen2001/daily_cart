<?php

namespace App\Jobs;

use App\Services\FirebaseCloudMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPublicPromotionPushJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public readonly string $appRole,
        public readonly string $title,
        public readonly string $message,
        public readonly array $data = [],
    ) {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 300, 900];
    }

    public function handle(FirebaseCloudMessagingService $firebase): void
    {
        $firebase->sendToTopic(
            'dailycart_public_promotions_'.$this->appRole,
            $this->title,
            $this->message,
            [
                ...$this->data,
                'type' => 'promotion',
                'app_role' => $this->appRole,
            ],
            'dailycart_'.$this->appRole,
        );
    }
}
