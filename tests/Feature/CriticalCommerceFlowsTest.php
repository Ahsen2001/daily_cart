<?php

namespace Tests\Feature;

use App\Jobs\SendNotificationChannelJob;
use App\Mail\GenericNotificationMail;
use App\Mail\OrderInvoiceMail;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\DeliveryFeeService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CriticalCommerceFlowsTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_registration_creates_one_complete_customer_identity(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Critical Path Customer',
            'email' => 'critical-registration@example.com',
            'phone' => '0773000001',
            'password' => 'Password123!',
            'device_name' => 'feature-test',
        ])->assertCreated();

        $user = User::query()->where('email', 'critical-registration@example.com')->firstOrFail();

        $response
            ->assertJsonPath('user.role', 'Customer')
            ->assertJsonStructure(['token', 'user']);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertNotNull($user->role_id);
        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'phone' => '0773000001',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_customer_cannot_update_or_remove_another_customers_cart_item(): void
    {
        [$owner, $ownerCustomer] = $this->createCustomer();
        [$attacker] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), stock: 10);
        $cart = Cart::query()->create(['customer_id' => $ownerCustomer->id, 'status' => 'active']);
        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100,
        ]);

        Sanctum::actingAs($attacker, ['customer']);

        $this->patchJson('/api/v1/cart-items/'.$item->id, ['quantity' => 5])->assertForbidden();
        $this->deleteJson('/api/v1/cart-items/'.$item->id)->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'cart_id' => $cart->id,
            'quantity' => 2,
        ]);
        $this->assertSame($owner->id, $cart->customer->user_id);
    }

    public function test_cart_api_rejects_a_variant_owned_by_a_different_product(): void
    {
        [$user] = $this->createCustomer();
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $selectedProduct = $this->createProduct($vendor, $category);
        $otherProduct = $this->createProduct($vendor, $category);
        $foreignVariant = ProductVariant::query()->create([
            'product_id' => $otherProduct->id,
            'name' => 'Cheaper foreign variant',
            'sku' => 'VAR-'.$this->nextSequence(),
            'price' => 1,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user, ['customer']);

        $this->postJson('/api/v1/cart', [
            'product_id' => $selectedProduct->id,
            'product_variant_id' => $foreignVariant->id,
            'quantity' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_variant_id');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_quote_matches_the_created_multi_vendor_order_totals(): void
    {
        Mail::fake();
        [$user, $customer] = $this->createCustomer();
        $category = $this->createCategory();
        $firstProduct = $this->createProduct($this->createVendor(), $category, price: 100);
        $secondProduct = $this->createProduct($this->createVendor(), $category, price: 150);
        $cart = $this->createCart($customer, [
            [$firstProduct, 1],
            [$secondProduct, 2],
        ]);

        Sanctum::actingAs($user, ['customer']);

        $quote = $this->postJson('/api/v1/checkout/quote')
            ->assertOk()
            ->json('quote');

        $response = $this->postJson('/api/v1/orders', $this->checkoutPayload())
            ->assertCreated();

        $createdTotal = round((float) collect($response->json('orders'))->sum('total_amount'), 2);

        $this->assertSame(2, Order::query()->count());
        $this->assertSame((float) $quote['grand_total'], $createdTotal);
        $this->assertSame((float) $quote['delivery_fee'], round((float) Order::query()->sum('delivery_fee'), 2));
        $this->assertSame('converted', $cart->refresh()->status);
    }

    public function test_placing_an_order_queues_email_and_sms_notifications_for_the_vendor(): void
    {
        Mail::fake();
        Bus::fake();

        [, $customer] = $this->createCustomer();
        $vendor = $this->createVendor();
        $product = $this->createProduct($vendor, $this->createCategory(), price: 250);
        $this->createCart($customer, [[$product, 1]]);

        app(OrderService::class)->createFromCart($customer, $this->checkoutPayload());

        Mail::assertQueued(GenericNotificationMail::class, fn (GenericNotificationMail $mail) => $mail->title === 'New order received'
            && $mail->hasTo($vendor->user->email));
        Bus::assertDispatched(
            SendNotificationChannelJob::class,
            fn (SendNotificationChannelJob $job) => $job->userId === $vendor->user->id
                && $job->channel === 'sms'
                && $job->title === 'New order received',
        );
    }

    public function test_rider_marking_an_order_out_for_delivery_queues_the_customer_invoice(): void
    {
        Mail::fake();

        [, $customer] = $this->createCustomer();
        $vendor = $this->createVendor();
        $product = $this->createProduct($vendor, $this->createCategory(), price: 250);
        $this->createCart($customer, [[$product, 1]]);
        $order = collect(app(OrderService::class)->createFromCart($customer, $this->checkoutPayload()))->firstOrFail();
        $rider = Rider::query()->create([
            'user_id' => User::factory()->create()->id,
            'vehicle_type' => 'motorbike',
            'address' => '1 Rider Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'availability_status' => 'delivering',
            'verification_status' => 'verified',
        ]);
        $delivery = $order->delivery;
        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        app(DeliveryService::class)->markOnTheWay($delivery->refresh());

        $this->assertSame('out_for_delivery', $order->refresh()->order_status);
        Mail::assertQueued(
            OrderInvoiceMail::class,
            fn (OrderInvoiceMail $mail) => $mail->order->is($order)
                && $mail->hasTo($customer->user->email),
        );
    }

    public function test_admin_delivery_fee_configuration_controls_quote_order_and_payment_totals(): void
    {
        Mail::fake();
        [, $customer] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), price: 1000);
        $cart = $this->createCart($customer, [[$product, 2]]);
        $rule = DeliveryFee::query()->create([
            'district' => 'Colombo',
            'base_fee' => 125,
            'per_km_fee' => 10,
            'minimum_order' => 500,
            'free_delivery_limit' => 5000,
            'status' => 'active',
        ]);
        $orders = app(OrderService::class);

        $quote = $orders->quote($cart, null, $customer, 0, 'Colombo', 2000);

        $this->assertSame(145.0, $quote['delivery_fee']);
        $this->assertSame(0.0, app(DeliveryFeeService::class)->calculate(5000, 'Colombo', 2000, 2, $customer));

        try {
            app(DeliveryFeeService::class)->calculate(400, 'Colombo', 2000, 1, $customer);
            $this->fail('Orders below the configured district minimum must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('delivery_district', $exception->errors());
        }

        $rule->update(['base_fee' => 300, 'per_km_fee' => 20]);
        $updatedQuote = $orders->quote($cart, null, $customer, 0, 'Colombo', 2000);

        $this->assertSame(340.0, $updatedQuote['delivery_fee']);

        $createdOrders = $orders->createFromCart($customer, $this->checkoutPayload([
            'delivery_district' => 'Colombo',
            'delivery_distance_meters' => 2000,
        ]));
        $order = collect($createdOrders)->first();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(340.0, (float) $order->delivery_fee);
        $this->assertSame($updatedQuote['grand_total'], (float) $order->total_amount);
        $this->assertSame(340.0, (float) $payment->delivery_fee);

        app(PaymentService::class)->syncPendingOrderAmounts($payment);

        $this->assertSame(340.0, (float) $order->refresh()->delivery_fee);
        $this->assertSame(340.0, (float) $payment->refresh()->delivery_fee);
    }

    public function test_admin_service_charge_configuration_is_used_at_checkout_and_preserved_for_payment(): void
    {
        Mail::fake();

        $adminRole = Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->put(route('admin.delivery-fees.service-charge.update'), ['service_charge_rate_percent' => 7.5])
            ->assertSessionHasNoErrors();

        [, $customer] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), price: 1000);
        $this->createCart($customer, [[$product, 1]]);
        $order = collect(app(OrderService::class)->createFromCart($customer, $this->checkoutPayload()))->firstOrFail();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(75.0, (float) $order->service_charge);
        $this->assertSame(75.0, (float) $payment->service_charge);

        $this->actingAs($admin)
            ->put(route('admin.delivery-fees.service-charge.update'), ['service_charge_rate_percent' => 10])
            ->assertSessionHasNoErrors();

        app(PaymentService::class)->syncPendingOrderAmounts($payment);

        $this->assertSame(75.0, (float) $order->refresh()->service_charge);
        $this->assertSame(75.0, (float) $payment->refresh()->service_charge);
    }

    public function test_only_admin_and_super_admin_can_manage_delivery_fee_configuration(): void
    {
        $route = RouteFacade::getRoutes()->getByName('admin.delivery-fees.store');

        $this->assertContains('role:Super Admin,Admin', $route->gatherMiddleware());

        foreach (['Admin', 'Super Admin'] as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $user = User::factory()->create(['role_id' => $role->id]);
            $user->assignRole($role);

            $this->actingAs($user)
                ->post(route('admin.delivery-fees.store'), [])
                ->assertSessionHasErrors(['district', 'base_fee', 'per_km_fee', 'minimum_order', 'status']);
        }

        foreach (['Customer', 'Vendor', 'Rider'] as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $user = User::factory()->create(['role_id' => $role->id]);
            $user->assignRole($role);

            $this->actingAs($user)
                ->post(route('admin.delivery-fees.store'), [])
                ->assertForbidden();
        }
    }

    public function test_competing_checkouts_cannot_oversell_the_last_unit(): void
    {
        Mail::fake();
        [, $firstCustomer] = $this->createCustomer();
        [, $secondCustomer] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), stock: 1);
        $this->createCart($firstCustomer, [[$product, 1]]);
        $this->createCart($secondCustomer, [[$product, 1]]);
        $orders = app(OrderService::class);

        $orders->createFromCart($firstCustomer, $this->checkoutPayload());

        try {
            $orders->createFromCart($secondCustomer, $this->checkoutPayload());
            $this->fail('The second checkout should not consume stock that has already been sold.');
        } catch (ValidationException $exception) {
            $this->assertNotEmpty($exception->errors());
        }

        $this->assertSame(0, $product->refresh()->stock_quantity);
        $this->assertSame('out_of_stock', $product->status);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_coupon_discount_is_quoted_recorded_and_limited_to_one_redemption(): void
    {
        Mail::fake();
        [$user, $customer] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), price: 200);
        $this->createCart($customer, [[$product, 1]]);
        $coupon = Coupon::query()->create([
            'code' => 'SAVE25',
            'title' => 'Save 25',
            'type' => 'fixed',
            'value' => 25,
            'discount_type' => 'fixed_amount',
            'discount_value' => 25,
            'minimum_order_amount' => 100,
            'usage_limit' => 10,
            'per_customer_limit' => 1,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'status' => 'active',
        ]);

        Sanctum::actingAs($user, ['customer']);

        $this->postJson('/api/v1/checkout/quote', ['coupon_code' => $coupon->code])
            ->assertOk()
            ->assertJsonPath('quote.discount', 25);

        $this->postJson('/api/v1/orders', $this->checkoutPayload(['coupon_code' => $coupon->code]))
            ->assertCreated();

        $order = Order::query()->sole();
        $this->assertSame('25.00', $order->discount_amount);
        $this->assertSame(1, $coupon->refresh()->used_count);
        $this->assertDatabaseHas('coupon_redemptions', [
            'coupon_id' => $coupon->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'discount_amount' => 25,
        ]);
    }

    public function test_wallet_checkout_debits_once_and_marks_the_payment_paid(): void
    {
        Mail::fake();
        [$user, $customer] = $this->createCustomer(walletBalance: 500);
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), price: 100);
        $this->createCart($customer, [[$product, 1]]);

        Sanctum::actingAs($user, ['customer']);

        $this->postJson('/api/v1/orders', $this->checkoutPayload(['payment_method' => 'wallet']))
            ->assertCreated();

        $order = Order::query()->with('payment')->sole();
        $expectedBalance = round(500 - (float) $order->total_amount, 2);

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('wallet', $order->payment->payment_method);
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame($expectedBalance, (float) $customer->refresh()->wallet_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'transaction_type' => 'payment',
            'type' => 'debit',
            'source' => 'order_payment',
            'reference' => $order->order_number,
        ]);
        $this->assertSame(1, $user->walletTransactions()->count());
    }

    /** @return array{User, Customer} */
    private function createCustomer(float $walletBalance = 0): array
    {
        $sequence = $this->nextSequence();
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '076'.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT),
        ]);
        $user->assignRole($role);
        $customer = Customer::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Customer '.$sequence,
            'phone' => $user->phone,
            'status' => 'active',
            'wallet_balance' => $walletBalance,
        ]);

        return [$user, $customer];
    }

    private function createVendor(): Vendor
    {
        $sequence = $this->nextSequence();
        $user = User::factory()->create([
            'phone' => '075'.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT),
        ]);

        return Vendor::query()->create([
            'user_id' => $user->id,
            'store_name' => 'Vendor '.$sequence,
            'phone' => $user->phone,
            'address' => '1 Vendor Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
    }

    private function createCategory(): Category
    {
        $sequence = $this->nextSequence();

        return Category::query()->create([
            'name' => 'Category '.$sequence,
            'slug' => 'category-'.$sequence,
            'status' => 'active',
        ]);
    }

    private function createProduct(
        Vendor $vendor,
        Category $category,
        float $price = 100,
        int $stock = 10
    ): Product {
        $sequence = $this->nextSequence();

        return Product::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.$sequence,
            'slug' => 'product-'.$sequence,
            'base_price' => $price,
            'price' => $price,
            'unit' => 'item',
            'unit_type' => 'item',
            'stock_quantity' => $stock,
            'status' => 'approved',
        ]);
    }

    /** @param array<int, array{Product, int}> $lines */
    private function createCart(Customer $customer, array $lines): Cart
    {
        $cart = Cart::query()->create(['customer_id' => $customer->id, 'status' => 'active']);

        foreach ($lines as [$product, $quantity]) {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);
        }

        return $cart;
    }

    /** @param array<string, mixed> $overrides */
    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'delivery_address' => '1 Test Street, Colombo',
            'scheduled_delivery_at' => now()->addHour()->toISOString(),
            'payment_method' => 'cash_on_delivery',
        ], $overrides);
    }

    private function nextSequence(): int
    {
        return ++$this->sequence;
    }
}
