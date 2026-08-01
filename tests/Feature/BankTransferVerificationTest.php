<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BankTransferService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankTransferVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_transfer_order_is_held_until_a_slip_is_verified(): void
    {
        Bus::fake();
        Storage::fake('local');

        $customerUser = User::factory()->create(['phone' => '0771000001']);
        $customer = Customer::forceCreate([
            'user_id' => $customerUser->id,
            'first_name' => 'Test Customer',
            'phone' => $customerUser->phone,
            'status' => 'active',
        ]);
        $vendorUser = User::factory()->create(['phone' => '0771000002']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'store_name' => 'Test Store',
            'phone' => $vendorUser->phone,
            'address' => '1 Store Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
        $category = Category::create(['name' => 'Test category', 'slug' => 'test-category', 'status' => 'active']);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Test product',
            'slug' => 'test-product',
            'base_price' => 250,
            'price' => 250,
            'unit' => 'item',
            'unit_type' => 'item',
            'stock_quantity' => 10,
            'status' => 'approved',
        ]);
        $cart = Cart::create(['customer_id' => $customer->id, 'status' => 'active']);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 250]);

        $order = app(OrderService::class)->createFromCart($customer, [
            'delivery_address' => '1 Customer Road, Colombo',
            'scheduled_delivery_at' => now()->addHour(),
            'payment_method' => 'bank_transfer',
        ])[0];
        $payment = Payment::where('order_id', $order->id)->firstOrFail();
        $transfer = $payment->bankTransferPayment()->firstOrFail();

        $this->assertSame('pending_upload', $transfer->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertDatabaseMissing('notifications', ['user_id' => $vendorUser->id, 'type' => 'new_order']);

        $transfer = app(BankTransferService::class)->uploadSlip(
            $transfer,
            UploadedFile::fake()->create('payment-slip.pdf', 120, 'application/pdf'),
            $customerUser,
        );
        $this->assertSame('pending_verification', $transfer->status);
        $this->assertDatabaseCount('payment_slips', 1);

        $admin = User::factory()->create(['phone' => '0771000003']);
        app(BankTransferService::class)->approve($transfer, $admin, 'Amount and reference match.');

        $this->assertSame('verified', $transfer->refresh()->status);
        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertSame('paid', $order->refresh()->payment_status);
        $this->assertDatabaseHas('notifications', ['user_id' => $vendorUser->id, 'type' => 'new_order']);
        $this->assertDatabaseHas('financial_verification_logs', ['bank_transfer_payment_id' => $transfer->id, 'action' => 'approved']);
    }
}
