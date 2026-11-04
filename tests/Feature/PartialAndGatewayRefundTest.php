<?php

namespace Tests\Feature;

use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\Role;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class PartialAndGatewayRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_wallet_refund_preserves_order_and_reverses_loyalty_proportionally(): void
    {
        Bus::fake();
        [$order, $payment, $customer, $admin] = $this->paidOrder('wallet');
        LoyaltyPoint::create([
            'customer_id' => $customer->id, 'order_id' => $order->id,
            'points' => 10, 'type' => 'earned', 'description' => 'Order reward', 'balance_after' => 10,
        ]);

        $first = app(RefundService::class)->request($order, 400, 'One item was damaged.');
        app(RefundService::class)->approve($first, $admin, 'Approved partial refund.');

        $this->assertSame('processed', $first->refresh()->status);
        $this->assertSame('partial', $first->refund_type);
        $this->assertSame('partially_refunded', $payment->refresh()->status);
        $this->assertSame(400.0, (float) $payment->refunded_amount);
        $this->assertSame('delivered', $order->refresh()->order_status);
        $this->assertSame(400.0, (float) $customer->refresh()->wallet_balance);
        $this->assertSame(-4, (int) LoyaltyPoint::where('order_id', $order->id)->where('type', 'reversed')->sum('points'));

        $second = app(RefundService::class)->request($order->refresh(), 600, 'Refund remaining order amount.');
        app(RefundService::class)->approve($second, $admin, 'Completes the refund.');

        $this->assertSame('refunded', $payment->refresh()->status);
        $this->assertSame(1000.0, (float) $payment->refunded_amount);
        $this->assertSame('refunded', $order->refresh()->order_status);
        $this->assertSame(1000.0, (float) $customer->refresh()->wallet_balance);
        $this->assertSame(-10, (int) LoyaltyPoint::where('order_id', $order->id)->where('type', 'reversed')->sum('points'));
    }

    public function test_payhere_partial_refund_uses_gateway_and_records_immutable_attempt_events(): void
    {
        Bus::fake();
        [$order, $payment, $customer, $admin] = $this->paidOrder('card');
        PaymentGatewayTransaction::create([
            'payment_id' => $payment->id, 'gateway' => 'payhere',
            'gateway_order_id' => $order->order_number, 'gateway_payment_id' => 'PH-PAY-123',
            'status' => 'paid', 'amount' => 1000, 'currency' => 'LKR', 'paid_at' => now(),
        ]);
        config([
            'payhere.app_id' => 'test-app', 'payhere.app_secret' => 'test-secret',
            'payhere.oauth_url' => 'https://sandbox.payhere.test/oauth/token',
            'payhere.refund_url' => 'https://sandbox.payhere.test/payment/refund',
        ]);
        Http::fake([
            'https://sandbox.payhere.test/oauth/token' => Http::response(['access_token' => 'access-123'], 200),
            'https://sandbox.payhere.test/payment/refund' => Http::response(['status' => 1, 'msg' => 'Successfully processed the refund', 'data' => 560034010257], 200),
        ]);

        $refund = app(RefundService::class)->request($order, 250, 'Partial gateway refund.');
        app(RefundService::class)->approve($refund, $admin, 'Approved through PayHere.');

        $this->assertSame('payhere_gateway', $refund->refresh()->refund_method);
        $this->assertSame('560034010257', $refund->gateway_refund_id);
        $this->assertSame('processed', $refund->status);
        $this->assertSame(0.0, (float) $customer->refresh()->wallet_balance);
        $this->assertSame('partially_refunded', $payment->refresh()->status);
        $this->assertSame(250.0, (float) $payment->refunded_amount);
        $this->assertSame(['initiated', 'succeeded'], $refund->attemptEvents()->orderBy('id')->pluck('event_type')->all());
        $this->assertDatabaseHas('refund_attempt_events', ['refund_id' => $refund->id, 'gateway_refund_id' => '560034010257']);
        try {
            $refund->attemptEvents()->firstOrFail()->update(['message' => 'tampered']);
            $this->fail('Refund attempt audit events must be immutable.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }
        Http::assertSent(fn ($request) => $request->url() === 'https://sandbox.payhere.test/payment/refund'
            && $request['payment_id'] === 'PH-PAY-123'
            && $request['amount'] === '250.00');
    }

    public function test_failed_payhere_refund_does_not_change_payment_or_credit_wallet(): void
    {
        Bus::fake();
        [$order, $payment, $customer, $admin] = $this->paidOrder('card');
        PaymentGatewayTransaction::create([
            'payment_id' => $payment->id, 'gateway' => 'payhere',
            'gateway_order_id' => $order->order_number, 'gateway_payment_id' => 'PH-PAY-FAIL',
            'status' => 'paid', 'amount' => 1000, 'currency' => 'LKR', 'paid_at' => now(),
        ]);
        config([
            'payhere.app_id' => 'test-app', 'payhere.app_secret' => 'test-secret',
            'payhere.oauth_url' => 'https://sandbox.payhere.test/oauth/token',
            'payhere.refund_url' => 'https://sandbox.payhere.test/payment/refund',
        ]);
        Http::fake([
            'https://sandbox.payhere.test/oauth/token' => Http::response(['access_token' => 'access-123'], 200),
            'https://sandbox.payhere.test/payment/refund' => Http::response(['status' => -1, 'msg' => 'Error processing refund', 'data' => null], 200),
        ]);
        $refund = app(RefundService::class)->request($order, 250, 'Gateway failure test.');

        try {
            app(RefundService::class)->approve($refund, $admin);
            $this->fail('A failed PayHere response must not approve the refund.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->assertSame('requested', $refund->refresh()->status);
        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame(0.0, (float) $payment->refunded_amount);
        $this->assertSame(0.0, (float) $customer->refresh()->wallet_balance);
        $this->assertSame(['initiated', 'failed'], $refund->attemptEvents()->orderBy('id')->pluck('event_type')->all());
    }

    private function paidOrder(string $method): array
    {
        $customerUser = $this->roleUser('Customer');
        $vendorUser = $this->roleUser('Vendor');
        $admin = $this->roleUser('Super Admin');
        $vendorUser->vendor->update(['status' => 'approved']);
        $order = Order::create([
            'order_number' => 'REF-'.uniqid(), 'customer_id' => $customerUser->customer->id,
            'vendor_id' => $vendorUser->vendor->id, 'subtotal' => 900, 'discount_amount' => 0,
            'delivery_fee' => 100, 'tax_amount' => 0, 'total_amount' => 1000, 'currency' => 'LKR',
            'delivery_address' => '479 Test Road', 'order_status' => 'delivered',
            'payment_status' => 'paid', 'placed_at' => now(), 'scheduled_delivery_at' => now(),
        ]);
        $payment = Payment::create([
            'order_id' => $order->id, 'payment_method' => $method, 'amount' => 1000,
            'currency' => 'LKR', 'status' => 'paid', 'paid_at' => now(),
        ]);

        return [$order, $payment, $customerUser->customer, $admin];
    }

    private function roleUser(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => '07'.random_int(10000000, 99999999)]);
        $user->assignRole($role);

        return $user->refresh();
    }
}
