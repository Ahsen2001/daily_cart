<?php

namespace Tests\Unit;

use App\Events\DeliveryLocationUpdated;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

class DeliveryLocationUpdatedTest extends TestCase
{
    public function test_it_uses_the_private_delivery_channel_and_exact_payload(): void
    {
        $payload = [
            'delivery_id' => 42,
            'location' => ['latitude' => 7.7, 'longitude' => 81.7],
        ];
        $event = new DeliveryLocationUpdated(42, $payload);

        $this->assertInstanceOf(PrivateChannel::class, $event->broadcastOn());
        $this->assertSame('private-delivery.42', $event->broadcastOn()->name);
        $this->assertSame('delivery.location.updated', $event->broadcastAs());
        $this->assertSame($payload, $event->broadcastWith());

        config(['broadcasting.default' => 'null']);
        $this->assertFalse($event->broadcastWhen());
        config(['broadcasting.default' => 'reverb']);
        $this->assertTrue($event->broadcastWhen());
    }
}
