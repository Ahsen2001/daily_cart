<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayCallbackEvent;
use App\Models\PaymentGatewayTransaction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayHereCallbackSafetyTest extends TestCase
{
    use RefreshDatabase;

    private int $phoneSequence = 0;

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
            'gateway_payment_id' => 'PH-10001',
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
            'payment_id' => 'PH-10001',
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
        $this->phoneSequence++;

        return '07741'.str_pad((string) $this->phoneSequence, 5, '0', STR_PAD_LEFT);
    }
}
