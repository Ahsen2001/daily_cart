<?php

namespace Tests\Feature;

use App\Http\Resources\OrderResource;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActiveDeliveryTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_cannot_submit_a_point_for_another_riders_delivery(): void
    {
        [, $assignedRider, , $delivery] = $this->activeDelivery();
        Sanctum::actingAs($assignedRider, ['rider']);
        $token = $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertOk()
            ->json('tracking_token');

        $otherRider = $this->userForRole('Rider', '0772000998');
        $otherRider->rider()->update(['verification_status' => 'verified']);
        Sanctum::actingAs($otherRider->refresh(), ['rider']);

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/location-updates", [
            'tracking_token' => $token,
            'points' => [$this->locationPoint($delivery, (string) Str::uuid(), 1)],
        ])->assertForbidden();

        $this->assertDatabaseCount('rider_locations', 0);
    }

    public function test_rider_cannot_submit_points_after_delivery_completion(): void
    {
        [, $rider, , $delivery] = $this->activeDelivery();
        Sanctum::actingAs($rider, ['rider']);
        $token = $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertOk()
            ->json('tracking_token');

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/location-updates", [
            'tracking_token' => $token,
            'points' => [$this->locationPoint($delivery, (string) Str::uuid(), 1)],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('rider_locations', 0);
    }

    public function test_only_the_assigned_rider_can_start_and_submit_active_delivery_locations(): void
    {
        [$customer, $rider, $order, $delivery] = $this->activeDelivery();
        Sanctum::actingAs($rider, ['auth', 'rider']);

        $token = $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertOk()
            ->assertJsonPath('delivery_id', $delivery->id)
            ->json('tracking_token');

        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/locations", [
            'tracking_token' => $token,
            'latitude' => 7.717006,
            'longitude' => 81.700112,
            'accuracy' => 8.5,
            'recorded_at' => now()->toIso8601String(),
        ])->assertCreated();

        $this->assertDatabaseHas('rider_locations', [
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->rider->id,
        ]);
        $this->assertDatabaseHas('delivery_tracking_states', [
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->rider->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($customer, ['customer']);
        $this->getJson("/api/v1/orders/{$order->id}/tracking")
            ->assertOk()
            ->assertJsonPath('tracking.is_active', true)
            ->assertJsonPath('location.latitude', 7.717006);

        $otherRider = $this->userForRole('Rider', '0772000999');
        $otherRider->rider()->update(['verification_status' => 'verified']);
        Sanctum::actingAs($otherRider, ['rider']);
        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertForbidden();
    }

    public function test_terminal_status_reassignment_and_logout_invalidate_tracking(): void
    {
        [, $rider, , $delivery] = $this->activeDelivery();
        Sanctum::actingAs($rider, ['auth', 'rider']);
        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")->assertOk();

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->assertDatabaseHas('delivery_tracking_states', [
            'delivery_id' => $delivery->id,
            'status' => 'stopped',
            'stop_reason' => 'delivery_delivered',
            'tracking_token_hash' => null,
        ]);

        $delivery->update(['status' => 'accepted', 'delivered_at' => null]);
        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")->assertOk();
        $this->postJson('/api/v1/logout')->assertOk();
        $this->assertDatabaseHas('delivery_tracking_states', [
            'delivery_id' => $delivery->id,
            'status' => 'stopped',
            'stop_reason' => 'rider_signed_out',
        ]);
    }

    public function test_offline_batches_are_idempotent_and_only_newer_points_replace_current_location(): void
    {
        [$customer, $rider, $order, $delivery] = $this->activeDelivery();
        Sanctum::actingAs($rider, ['rider']);
        $token = $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertOk()
            ->json('tracking_token');
        $olderUuid = (string) Str::uuid();
        $newerUuid = (string) Str::uuid();
        $points = [
            [
                'point_uuid' => $newerUuid,
                'delivery_id' => $delivery->id,
                'latitude' => 7.7172000,
                'longitude' => 81.7002000,
                'accuracy_meters' => 7.0,
                'heading' => 90,
                'speed' => 8,
                'recorded_at' => now()->subSeconds(5)->toIso8601String(),
                'sequence_number' => 2,
            ],
            [
                'point_uuid' => $olderUuid,
                'delivery_id' => $delivery->id,
                'latitude' => 7.7171000,
                'longitude' => 81.7001000,
                'accuracy_meters' => 8.0,
                'heading' => 80,
                'speed' => 7,
                'recorded_at' => now()->subSeconds(15)->toIso8601String(),
                'sequence_number' => 1,
            ],
        ];

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/location-updates", [
            'tracking_token' => $token,
            'points' => $points,
        ])->assertAccepted()
            ->assertJsonPath('accepted_count', 2)
            ->assertJsonPath('duplicate_count', 0);

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/location-updates", [
            'tracking_token' => $token,
            'points' => $points,
        ])->assertAccepted()
            ->assertJsonPath('accepted_count', 0)
            ->assertJsonPath('duplicate_count', 2);

        $this->assertDatabaseCount('rider_locations', 2);
        $this->assertDatabaseHas('delivery_current_locations', [
            'delivery_id' => $delivery->id,
            'rider_id' => $rider->rider->id,
            'latitude' => 7.7172000,
            'sequence_number' => 2,
        ]);

        Sanctum::actingAs($customer, ['customer']);
        $this->getJson("/api/v1/orders/{$order->id}/tracking")
            ->assertOk()
            ->assertJsonPath('delivery_status', 'accepted')
            ->assertJsonPath('location.latitude', 7.7172)
            ->assertJsonPath('location.is_stale', false);

        $otherCustomer = $this->userForRole('Customer', '0771000999');
        Sanctum::actingAs($otherCustomer, ['customer']);
        $this->getJson("/api/v1/orders/{$order->id}/tracking")->assertForbidden();

        $delivery->update(['status' => 'delivered', 'delivered_at' => now()]);
        Sanctum::actingAs($customer, ['customer']);
        $this->getJson("/api/v1/orders/{$order->id}/tracking")
            ->assertOk()
            ->assertJsonPath('tracking.is_active', false)
            ->assertJsonPath('location', null);
    }

    public function test_private_delivery_channel_authorizes_only_delivery_participants_and_admins(): void
    {
        [$customer, $rider, $order, $delivery] = $this->activeDelivery();
        $vendor = $order->vendor()->with('user')->firstOrFail()->user;
        $admin = $this->userForRole('Admin', '0774000101');
        $outsider = $this->userForRole('Customer', '0771000997');
        $payload = [
            'socket_id' => '1234.5678',
            'channel_name' => "private-delivery.{$delivery->id}",
        ];

        foreach ([$customer, $rider, $vendor, $admin] as $authorizedUser) {
            Sanctum::actingAs($authorizedUser);
            $this->postJson('/broadcasting/auth', $payload)->assertOk();
        }

        Sanctum::actingAs($outsider);
        $this->postJson('/broadcasting/auth', $payload)->assertForbidden();
    }

    public function test_order_resource_includes_current_location_and_recorded_at(): void
    {
        [, $rider, $order, $delivery] = $this->activeDelivery();
        Sanctum::actingAs($rider, ['rider']);
        $token = $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/tracking/start")
            ->assertOk()
            ->json('tracking_token');
        $recordedAt = now()->subSeconds(5)->startOfSecond();

        $this->postJson("/api/v1/rider/deliveries/{$delivery->id}/location-updates", [
            'tracking_token' => $token,
            'points' => [[
                ...$this->locationPoint($delivery, (string) Str::uuid(), 1),
                'recorded_at' => $recordedAt->toIso8601String(),
            ]],
        ])->assertAccepted();

        $resource = (new OrderResource($order->refresh()->load([
            'payment',
            'delivery.rider.user',
            'delivery.trackingState',
            'delivery.currentLocation',
        ])))->resolve();

        $this->assertSame(7.7172, $resource['rider']['latitude']);
        $this->assertSame(81.7002, $resource['rider']['longitude']);
        $this->assertNotNull($resource['rider']['location_recorded_at']);
        $this->assertSame(
            $recordedAt->toIso8601String(),
            $resource['rider']['location_recorded_at']->toIso8601String(),
        );
    }

    /** @return array{User, User, Order, Delivery} */
    private function activeDelivery(): array
    {
        $customer = $this->userForRole('Customer', '0771000101');
        $rider = $this->userForRole('Rider', '0772000101');
        $vendorUser = $this->userForRole('Vendor', '0773000101');
        $rider->rider()->update([
            'verification_status' => 'verified',
            'availability_status' => 'delivering',
        ]);
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update(['status' => 'approved']);

        $order = Order::query()->create([
            'order_number' => 'TRACK-'.uniqid(),
            'customer_id' => $customer->customer()->firstOrFail()->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100,
            'total_amount' => 100,
            'delivery_address' => '479 Ex-Chairman Road, Oddamavadi',
            'delivery_latitude' => 7.7170000,
            'delivery_longitude' => 81.7000000,
            'order_status' => 'assigned_to_rider',
            'payment_status' => 'paid',
            'placed_at' => now(),
            'scheduled_delivery_at' => now(),
        ]);
        $delivery = Delivery::query()->create([
            'order_id' => $order->id,
            'rider_id' => $rider->rider()->firstOrFail()->id,
            'pickup_address' => 'DailyCart Vendor Store',
            'delivery_address' => $order->delivery_address,
            'scheduled_at' => now(),
            'accepted_at' => now(),
            'status' => 'accepted',
        ]);

        return [$customer->refresh(), $rider->refresh(), $order->refresh(), $delivery->refresh()];
    }

    private function userForRole(string $roleName, string $phone): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => $phone]);
        $user->assignRole($role);

        return $user->refresh();
    }

    /** @return array<string, int|float|string> */
    private function locationPoint(Delivery $delivery, string $uuid, int $sequence): array
    {
        return [
            'point_uuid' => $uuid,
            'delivery_id' => $delivery->id,
            'latitude' => 7.7172,
            'longitude' => 81.7002,
            'accuracy_meters' => 7.0,
            'heading' => 90,
            'speed' => 8,
            'recorded_at' => now()->subSeconds(5)->toIso8601String(),
            'sequence_number' => $sequence,
        ];
    }
}
