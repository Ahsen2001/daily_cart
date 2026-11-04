<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayCallbackEvent;
use App\Models\PaymentGatewayTransaction;
use App\Models\Role;
use App\Models\User;
use App\Services\PayHereService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayHereCallbackSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payhere.merchant_id' => '1210000',
            'payhere.merchant_secret' => 'sandbox-secret',
        ]);
    }

    public function test_valid_success_is_idempotent_and_retains_one_immutable_event(): void
    {
        [$payment] = $this->pendingPayment();
        $payload = $this->signedPayload($payment, '2');

        $this->post(route('payhere.notify'), $payload)->assertOk()->assertSeeText('OK');
        $this->post(route('payhere.notify'), $payload)->assertOk()->assertSeeText('OK');

        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('paid', $payment->order->refresh()->payment_status);
        $this->assertDatabaseCount('payment_gateway_callback_events', 1);
        $this->assertDatabaseHas('payment_gateway_callback_events', [
            'payment_id' => $payment->id,
            'gateway_payment_id' => 'PH-'.$payment->id,
            'normalized_status' => 'paid',
            'signature_valid' => true,
            'identity_valid' => true,
            'processing_result' => 'applied',
        ]);
    }

    public function test_late_failed_callback_cannot_overwrite_a_paid_payment_or_transaction(): void
    {
        [$payment, $transaction] = $this->pendingPayment();

        $this->post(route('payhere.notify'), $this->signedPayload($payment, '2'))->assertOk();
        $this->post(route('payhere.notify'), $this->signedPayload($payment, '-2'))->assertOk();

        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('paid', $payment->order->refresh()->payment_status);
        $this->assertSame('paid', $transaction->refresh()->status);
        $this->assertDatabaseHas('payment_gateway_callback_events', [
            'payment_id' => $payment->id,
            'status_code' => '-2',
            'processing_result' => 'ignored_transition',
        ]);
    }

    public function test_initial_failed_and_cancelled_callbacks_follow_the_legal_transition(): void
    {
        [$failedPayment, $failedTransaction] = $this->pendingPayment('SAFE-FAILED');
        $this->post(route('payhere.notify'), $this->signedPayload($failedPayment, '-2'))->assertOk();

        [$cancelledPayment, $cancelledTransaction] = $this->pendingPayment('SAFE-CANCELLED');
        $this->post(route('payhere.notify'), $this->signedPayload($cancelledPayment, '-1'))->assertOk();

        $this->assertSame('failed', $failedPayment->refresh()->status);
        $this->assertSame('failed', $failedTransaction->refresh()->status);
        $this->assertSame('failed', $cancelledPayment->refresh()->status);
        $this->assertSame('failed', $cancelledTransaction->refresh()->status);
        $this->assertDatabaseHas('payment_gateway_callback_events', [
            'payment_id' => $failedPayment->id,
            'status_code' => '-2',
            'processing_result' => 'applied',
        ]);
        $this->assertDatabaseHas('payment_gateway_callback_events', [
            'payment_id' => $cancelledPayment->id,
            'status_code' => '-1',
            'processing_result' => 'applied',
        ]);
    }

    public function test_callback_amount_currency_and_merchant_must_match_the_checkout(): void
    {
        [$amountPayment] = $this->pendingPayment('SAFE-AMOUNT');
        $amountPayload = $this->signedPayload($amountPayment, '2', [
            'payhere_amount' => '99.99',
        ]);
        $this->post(route('payhere.notify'), $amountPayload)
            ->assertOk()
            ->assertSeeText('INVALID_IDENTITY');

        [$currencyPayment] = $this->pendingPayment('SAFE-CURRENCY');
        $currencyPayload = $this->signedPayload($currencyPayment, '2', [
            'payhere_currency' => 'USD',
        ]);
        $this->post(route('payhere.notify'), $currencyPayload)
            ->assertOk()
            ->assertSeeText('INVALID_IDENTITY');

        [$merchantPayment] = $this->pendingPayment('SAFE-MERCHANT');
        $merchantPayload = $this->signedPayload($merchantPayment, '2', [
            'merchant_id' => '9999999',
        ]);
        $this->post(route('payhere.notify'), $merchantPayload)
            ->assertOk()
            ->assertSeeText('INVALID_IDENTITY');

        $this->assertSame('pending', $amountPayment->refresh()->status);
        $this->assertSame('pending', $currencyPayment->refresh()->status);
        $this->assertSame('pending', $merchantPayment->refresh()->status);
        $this->assertSame(3, PaymentGatewayCallbackEvent::query()->where('processing_result', 'rejected')->count());
    }

    public function test_gateway_order_and_payment_identity_are_enforced(): void
    {
        [$payment] = $this->pendingPayment('SAFE-IDENTITY');
        $unknownOrder = $this->signedPayload($payment, '2', ['order_id' => 'NOT-STORED']);
        $this->post(route('payhere.notify'), $unknownOrder)
            ->assertOk()
            ->assertSeeText('ORDER_NOT_FOUND');

        $this->post(route('payhere.notify'), $this->signedPayload($payment, '0'))->assertOk();
        $differentPayment = $this->signedPayload($payment, '2', ['payment_id' => 'PH-OTHER']);
        $this->post(route('payhere.notify'), $differentPayment)
            ->assertOk()
            ->assertSeeText('INVALID_IDENTITY');

        $this->assertSame('pending', $payment->refresh()->status);
    }

    public function test_invalid_signature_is_audited_without_changing_payment_state(): void
    {
        [$payment] = $this->pendingPayment('SAFE-SIGNATURE');
        $payload = $this->signedPayload($payment, '2');
        $payload['md5sig'] = str_repeat('0', 32);

        $this->post(route('payhere.notify'), $payload)
            ->assertOk()
            ->assertSeeText('INVALID_SIGNATURE');

        $this->assertSame('pending', $payment->refresh()->status);
        $this->assertDatabaseHas('payment_gateway_callback_events', [
            'gateway_order_id' => 'SAFE-SIGNATURE',
            'signature_valid' => false,
            'identity_valid' => false,
            'processing_result' => 'rejected',
        ]);
    }

    public function test_concurrent_duplicate_callbacks_are_applied_once(): void
    {
        [$payment] = $this->pendingPayment('SAFE-CONCURRENT-'.uniqid());
        $payload = $this->signedPayload($payment, '0');
        $order = $payment->order()->with(['customer', 'vendor'])->firstOrFail();
        $customer = $order->customer;
        $vendor = $order->vendor;
        $userIds = [$customer->user_id, $vendor->user_id];

        // Child PHP processes need committed fixtures. This is exclusively the
        // isolated dailycart_test database; application data is never used.
        DB::commit();
        try {
            $callbacks = array_fill(0, 2, function () use ($payload) {
                config([
                    'database.default' => 'mysql',
                    'database.connections.mysql.database' => 'dailycart_test',
                    'payhere.merchant_id' => '1210000',
                    'payhere.merchant_secret' => 'sandbox-secret',
                ]);
                DB::purge('mysql');
                DB::reconnect('mysql');

                return app(PayHereService::class)->processNotification(
                    $payload,
                    app(PaymentService::class),
                );
            });
            $results = Concurrency::run($callbacks);

            $this->assertSame(['OK', 'OK'], array_values($results));
            $this->assertSame(1, PaymentGatewayCallbackEvent::query()
                ->where('payment_id', $payment->id)
                ->where('status_code', '0')
                ->count());
            $this->assertSame('pending', $payment->refresh()->status);
        } finally {
            DB::table('payment_gateway_callback_events')->where('payment_id', $payment->id)->delete();
            DB::table('payment_gateway_transactions')->where('payment_id', $payment->id)->delete();
            DB::table('payments')->where('id', $payment->id)->delete();
            DB::table('order_status_history')->where('order_id', $order->id)->delete();
            DB::table('orders')->where('id', $order->id)->delete();
            DB::table('customers')->where('id', $customer->id)->delete();
            DB::table('vendors')->where('id', $vendor->id)->delete();
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $userIds)
                ->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
            // Balance RefreshDatabase's teardown transaction after the manual
            // commit required to expose fixtures to the child processes.
            DB::beginTransaction();
        }
    }

    /** @return array{Payment, PaymentGatewayTransaction} */
    private function pendingPayment(?string $orderNumber = null): array
    {
        $customer = $this->userForRole('Customer', $this->nextPhone());
        $vendorUser = $this->userForRole('Vendor', $this->nextPhone());
        $vendor = $vendorUser->vendor()->firstOrFail();
        $vendor->update(['status' => 'approved']);
        $orderNumber ??= 'PAYHERE-'.uniqid();

        $order = Order::query()->create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->customer()->firstOrFail()->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 100,
            'total_amount' => 100,
            'currency' => 'LKR',
            'delivery_address' => '479 Ex-Chairman Road, Oddamavadi',
            'order_status' => 'pending',
            'payment_status' => 'pending',
            'placed_at' => now(),
            'scheduled_delivery_at' => now()->addHour(),
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'card',
            'amount' => 100,
            'currency' => 'LKR',
            'status' => 'pending',
        ]);
        $transaction = PaymentGatewayTransaction::query()->create([
            'payment_id' => $payment->id,
            'gateway' => 'payhere',
            'gateway_order_id' => $orderNumber,
            'status' => 'pending',
            'amount' => 100,
            'currency' => 'LKR',
            'request_payload' => ['merchant_id' => '1210000'],
        ]);

        return [$payment->refresh(), $transaction->refresh()];
    }

    /** @return array<string, string> */
    private function signedPayload(Payment $payment, string $statusCode, array $overrides = []): array
    {
        $payload = array_merge([
            'merchant_id' => '1210000',
            'order_id' => $payment->order->order_number,
            'payment_id' => 'PH-'.$payment->id,
            'payhere_amount' => '100.00',
            'payhere_currency' => 'LKR',
            'status_code' => $statusCode,
        ], $overrides);
        $payload['md5sig'] = strtoupper(md5(
            $payload['merchant_id'].
            $payload['order_id'].
            $payload['payhere_amount'].
            $payload['payhere_currency'].
            $payload['status_code'].
            strtoupper(md5('sandbox-secret'))
        ));

        return $payload;
    }

    private function userForRole(string $roleName, string $phone): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['role_id' => $role->id, 'phone' => $phone]);
        $user->assignRole($role);

        return $user->refresh();
    }

    private function nextPhone(): string
    {
        return '077'.str_pad((string) random_int(0, 9_999_999), 7, '0', STR_PAD_LEFT);
    }
}
