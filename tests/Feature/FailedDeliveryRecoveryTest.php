<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryRecoveryCase;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Services\DeliveryRecoveryService;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FailedDeliveryRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_delivery_creates_structured_case_with_immutable_history_and_sla(): void
    {
        Bus::fake();
        [$delivery, $riderUser] = $this->deliveryContext();

        app(DeliveryService::class)->markFailed($delivery, 'vehicle_breakdown', 'Rear tyre puncture.', $riderUser);

        $case = DeliveryRecoveryCase::firstOrFail();
        $this->assertSame('vehicle_breakdown', $delivery->refresh()->failed_reason_category);
        $this->assertSame('open', $case->status);
        $this->assertTrue($case->escalation_due_at->isFuture());
        $this->assertDatabaseHas('delivery_recovery_events', ['delivery_recovery_case_id' => $case->id, 'event_type' => 'failure_reported']);
        $this->assertSame('available', $delivery->rider->refresh()->availability_status);
    }

    public function test_admin_can_retry_failed_delivery_with_same_rider(): void
    {
        Bus::fake();
        [$delivery, $riderUser] = $this->deliveryContext();
        app(DeliveryService::class)->markFailed($delivery, 'customer_unavailable', 'Customer did not answer.', $riderUser);
        $admin = $this->roleUser('Admin');

        $case = app(DeliveryRecoveryService::class)->retry(DeliveryRecoveryCase::firstOrFail(), $admin, 'Retry in 20 minutes.');

        $this->assertSame('resolved', $case->status);
        $this->assertSame('retry_started', $case->resolution);
        $this->assertSame('assigned', $delivery->refresh()->status);
        $this->assertSame('assigned_to_rider', $delivery->order->refresh()->order_status);
        $this->assertSame('delivering', $delivery->rider->refresh()->availability_status);
    }

    public function test_admin_can_reassign_failed_delivery_and_audit_both_riders(): void
    {
        Bus::fake();
        [$delivery, $riderUser] = $this->deliveryContext();
        app(DeliveryService::class)->markFailed($delivery, 'rider_incident', 'Rider cannot continue.', $riderUser);
        $newRiderUser = $this->roleUser('Rider');
        $newRiderUser->rider->update(['verification_status' => 'verified', 'availability_status' => 'available']);

        $case = app(DeliveryRecoveryService::class)->reassign(DeliveryRecoveryCase::firstOrFail(), $newRiderUser->rider->refresh(), $this->roleUser('Admin'), 'Nearest available rider.');

        $this->assertSame('reassigned', $case->resolution);
        $this->assertSame($newRiderUser->rider->id, $delivery->refresh()->rider_id);
        $metadata = $case->events()->where('event_type', 'reassigned')->firstOrFail()->metadata;
        $this->assertArrayHasKey('previous_rider_id', $metadata);
        $this->assertSame($newRiderUser->rider->id, $metadata['rider_id']);
    }

    public function test_overdue_open_case_is_escalated_once(): void
    {
        Bus::fake();
        [$delivery, $riderUser] = $this->deliveryContext();
        app(DeliveryService::class)->markFailed($delivery, 'other', 'Unexpected obstruction.', $riderUser);
        DeliveryRecoveryCase::firstOrFail()->update(['escalation_due_at' => now()->subMinute()]);

        $this->assertSame(1, app(DeliveryRecoveryService::class)->escalateOverdue());
        $this->assertSame(0, app(DeliveryRecoveryService::class)->escalateOverdue());
        $this->assertNotNull(DeliveryRecoveryCase::first()->escalated_at);
        $this->assertDatabaseCount('delivery_recovery_events', 2);
    }

    public function test_return_to_vendor_requires_and_records_refund_decision_for_paid_order(): void
    {
        Bus::fake();
        [$delivery, $riderUser] = $this->deliveryContext();
        Payment::create([
            'order_id' => $delivery->order_id, 'payment_method' => 'card',
            'amount' => 1100, 'currency' => 'LKR', 'status' => 'paid', 'paid_at' => now(),
        ]);
        $delivery->order()->update(['payment_status' => 'paid']);
        app(DeliveryService::class)->markFailed($delivery, 'damaged_items', 'Items damaged in transit.', $riderUser);
        $service = app(DeliveryRecoveryService::class);
        $admin = $this->roleUser('Super Admin');
        $case = DeliveryRecoveryCase::firstOrFail();

        $service->startReturn($case, $admin, 'Return all items.');
        $case = $service->completeReturn($case->refresh(), $admin, 'Vendor confirmed receipt.');
        $this->assertSame('refund_pending', $case->status);
        $this->assertSame('pending', $case->refund_decision);

        $case = $service->approveRefund($case, $admin, 1100, 'Full refund after failed delivery.');
        $this->assertSame('resolved', $case->status);
        $this->assertSame('refund_approved', $case->resolution);
        $this->assertSame('refunded', $delivery->order->payment->refresh()->status);
        $this->assertSame(1100.0, (float) $delivery->order->customer->refresh()->wallet_balance);
    }

    private function deliveryContext(): array
    {
        $customerUser = $this->roleUser('Customer');
        $vendorUser = $this->roleUser('Vendor');
        $riderUser = $this->roleUser('Rider');
        $vendorUser->vendor->update(['status' => 'approved']);
        $riderUser->rider->update(['verification_status' => 'verified', 'availability_status' => 'delivering']);
        $order = Order::create([
            'order_number' => 'REC-'.uniqid(), 'customer_id' => $customerUser->customer->id,
            'vendor_id' => $vendorUser->vendor->id, 'subtotal' => 1000, 'discount_amount' => 0,
            'delivery_fee' => 100, 'tax_amount' => 0, 'total_amount' => 1100, 'currency' => 'LKR',
            'delivery_address' => '479 Test Road', 'order_status' => 'assigned_to_rider',
            'payment_status' => 'pending', 'placed_at' => now(), 'scheduled_delivery_at' => now()->addHour(),
        ]);
        $delivery = Delivery::create([
            'order_id' => $order->id, 'rider_id' => $riderUser->rider->id,
            'pickup_address' => 'Vendor address', 'delivery_address' => $order->delivery_address,
            'scheduled_at' => $order->scheduled_delivery_at, 'status' => 'accepted', 'accepted_at' => now(),
        ]);

        return [$delivery, $riderUser];
    }

    private function roleUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => '07'.random_int(10000000, 99999999)]);
        $user->assignRole($role);

        return $user->refresh();
    }
}
