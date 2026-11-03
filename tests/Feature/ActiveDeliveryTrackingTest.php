<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActiveDeliveryTrackingTest extends TestCase
{
    use RefreshDatabase;

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
}
