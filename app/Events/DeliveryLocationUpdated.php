<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $deliveryId,
        public readonly array $payload,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('delivery.'.$this->deliveryId);
    }

    public function broadcastAs(): string
    {
        return 'delivery.location.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }

    public function broadcastWhen(): bool
    {
        // The log driver would persist customer coordinates in application
        // logs. Wait for an actual private WebSocket provider instead.
        return ! in_array(config('broadcasting.default'), ['log', 'null', null], true);
    }
}
