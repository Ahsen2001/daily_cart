<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\RiderRating;
use App\Models\Role;
use App\Models\User;
use App\Services\RiderRatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RiderRatingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_rate_only_their_completed_delivery_and_update_the_same_rating(): void
    {
        Bus::fake();
        [$customerUser, $order] = $this->completedOrder();

        Sanctum::actingAs($customerUser, ['customer']);

        $this->postJson("/api/v1/orders/{$order->id}/rider-rating", [
            'rating' => 5,
            'tags' => ['on_time', 'friendly'],
            'comment' => 'Fast and careful delivery.',
        ])
            ->assertCreated()
            ->assertJsonPath('rating.rating', 5);

        $this->postJson("/api/v1/orders/{$order->id}/rider-rating", [
            'rating' => 4,
            'tags' => ['good_communication'],
            'comment' => 'Updated feedback.',
        ])
            ->assertOk()
            ->assertJsonPath('rating.rating', 4);

        $this->assertDatabaseCount('rider_ratings', 1);
        $this->assertDatabaseHas('rider_ratings', [
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Updated feedback.',
        ]);

        [, $pendingOrder] = $this->completedOrder('pending', 'assigned');

        $this->postJson("/api/v1/orders/{$pendingOrder->id}/rider-rating", ['rating' => 5])
            ->assertForbidden();
    }

    public function test_rider_can_view_and_report_only_their_own_feedback(): void
    {
        Bus::fake();
        [$customerUser, $order, $riderUser] = $this->completedOrder();
        Sanctum::actingAs($customerUser, ['customer']);
        $this->postJson("/api/v1/orders/{$order->id}/rider-rating", [
            'rating' => 2,
            'comment' => 'The parcel arrived late.',
        ])->assertCreated();

        $rating = RiderRating::query()->sole();
        Sanctum::actingAs($riderUser, ['rider']);

        $this->getJson('/api/v1/rider/ratings')
            ->assertOk()
            ->assertJsonPath('statistics.average', 2)
            ->assertJsonPath('statistics.count', 1)
            ->assertJsonPath('ratings.0.comment', 'The parcel arrived late.')
            ->assertJsonMissingPath('ratings.0.customer');

        $this->patchJson("/api/v1/rider/ratings/{$rating->id}/report", [
            'reason' => 'This comment needs an administrator review.',
        ])
            ->assertOk()
            ->assertJsonPath('rating.status', 'reported')
            ->assertJsonPath('rating.report_reason', 'This comment needs an administrator review.');

        Sanctum::actingAs($customerUser, ['customer']);
        $this->getJson("/api/v1/orders/{$order->id}/rider-rating")
            ->assertOk()
            ->assertJsonPath('rating.report_reason', null);

        $this->assertDatabaseHas('rider_ratings', [
            'id' => $rating->id,
            'status' => 'reported',
        ]);
    }

    public function test_admin_can_moderate_a_reported_rating(): void
    {
        Bus::fake();
        [$customerUser, $order] = $this->completedOrder();
        Sanctum::actingAs($customerUser, ['customer']);
        $this->postJson("/api/v1/orders/{$order->id}/rider-rating", [
            'rating' => 1,
            'comment' => 'Administrator review requested.',
        ])->assertCreated();

        $rating = RiderRating::query()->sole();
        $rating->update(['status' => 'reported', 'report_reason' => 'Disputed feedback.']);
        $admin = $this->userForRole('Super Admin', '0773000003');

        $this->assertTrue($admin->can('moderate', $rating));
        app(RiderRatingService::class)->moderate($rating, $admin, 'hidden');

        $this->assertDatabaseHas('rider_ratings', [
            'id' => $rating->id,
            'status' => 'hidden',
            'moderated_by' => $admin->id,
        ]);
    }

    /** @return array{User, Order, User} */
    private function completedOrder(string $orderStatus = 'delivered', string $deliveryStatus = 'delivered'): array
    {
        $suffix = str_pad((string) User::query()->count(), 7, '0', STR_PAD_LEFT);
        $customerUser = $this->userForRole('Customer', '071'.$suffix);
        $riderUser = $this->userForRole('Rider', '072'.$suffix);
        $vendorUser = $this->userForRole('Vendor', '073'.$suffix);
        $riderUser->rider()->update([
            'verification_status' => 'verified',
            'availability_status' => 'available',
        ]);
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update(['status' => 'approved']);

        $order = Order::query()->create([
            'order_number' => 'RATING-'.uniqid(),
            'customer_id' => $customerUser->customer()->firstOrFail()->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100,
            'total_amount' => 100,
            'delivery_address' => '479 Ex-Chairman Road, Oddamavadi',
            'order_status' => $orderStatus,
            'payment_status' => 'paid',
            'placed_at' => now(),
            'scheduled_delivery_at' => now(),
        ]);
        Delivery::query()->create([
            'order_id' => $order->id,
            'rider_id' => $riderUser->rider()->firstOrFail()->id,
            'pickup_address' => 'DailyCart Vendor Store',
            'delivery_address' => $order->delivery_address,
            'scheduled_at' => now(),
            'delivered_at' => $deliveryStatus === 'delivered' ? now() : null,
            'status' => $deliveryStatus,
        ]);

        return [$customerUser, $order->refresh(), $riderUser];
    }

    private function userForRole(string $roleName, string $phone): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => $phone]);
        $user->assignRole($role);

        return $user->refresh();
    }
}
