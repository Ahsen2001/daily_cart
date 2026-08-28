<?php

namespace Tests\Feature;

use App\Jobs\DeliverNotificationChannelJob;
use App\Mail\OrderInvoiceMail;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Notification;
use App\Models\NotificationDeliveryLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\DeliveryFeeService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Storage;
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
            'password_confirmation' => 'Password123!',
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

    public function test_customer_can_add_selected_web_products_to_cart_in_one_request(): void
    {
        [$user, $customer] = $this->createCustomer();
        $category = $this->createCategory();
        $vendor = $this->createVendor();
        $firstProduct = $this->createProduct($vendor, $category, price: 100);
        $secondProduct = $this->createProduct($vendor, $category, price: 250);

        $this->actingAs($user)
            ->post(route('customer.cart.bulk'), [
                'items' => [
                    ['product_id' => $firstProduct->id, 'quantity' => 2, 'selected' => true],
                    ['product_id' => $secondProduct->id, 'quantity' => 3, 'selected' => true],
                ],
            ])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('status', '2 selected products were added to cart.');

        $cartId = Cart::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->value('id');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $secondProduct->id,
            'quantity' => 3,
        ]);
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
        DeliveryFee::query()->create([
            'district' => 'Default',
            'base_fee' => 80,
            'per_km_fee' => 0,
            'minimum_order' => 0,
            'status' => 'active',
        ]);
        Setting::query()->create([
            'setting_key' => 'service_charge_rate_percent',
            'setting_value' => 7.5,
        ]);

        Sanctum::actingAs($user, ['customer']);

        $quote = $this->postJson('/api/v1/checkout/quote')
            ->assertOk()
            ->json('quote');

        $response = $this->postJson('/api/v1/orders', $this->checkoutPayload())
            ->assertCreated();

        $createdTotal = round((float) collect($response->json('orders'))->sum('total_amount'), 2);

        $this->assertSame(2, Order::query()->count());
        $this->assertSame(80.0, (float) $quote['delivery_fee']);
        $this->assertSame(30.0, (float) $quote['service_charge']);
        $this->assertSame((float) $quote['grand_total'], $createdTotal);
        $this->assertSame((float) $quote['delivery_fee'], round((float) Order::query()->sum('delivery_fee'), 2));
        $this->assertSame((float) $quote['service_charge'], round((float) Order::query()->sum('service_charge'), 2));
        $this->assertSame('converted', $cart->refresh()->status);
    }

    public function test_placing_an_order_queues_customer_email_and_vendor_channel_notifications(): void
    {
        Mail::fake();
        Bus::fake();

        [$customerUser, $customer] = $this->createCustomer();
        $vendor = $this->createVendor();
        $product = $this->createProduct($vendor, $this->createCategory(), price: 250);
        $this->createCart($customer, [[$product, 1]]);

        app(OrderService::class)->createFromCart($customer, $this->checkoutPayload());

        $customerEmailDeliveryId = NotificationDeliveryLog::query()
            ->where('user_id', $customerUser->id)
            ->where('channel', 'email')
            ->value('id');
        $emailDeliveryId = NotificationDeliveryLog::query()
            ->where('user_id', $vendor->user->id)
            ->where('channel', 'email')
            ->value('id');
        $smsDeliveryId = NotificationDeliveryLog::query()
            ->where('user_id', $vendor->user->id)
            ->where('channel', 'sms')
            ->value('id');

        $this->assertNotNull($customerEmailDeliveryId);
        $this->assertNotNull($emailDeliveryId);
        $this->assertNotNull($smsDeliveryId);
        Bus::assertDispatched(
            DeliverNotificationChannelJob::class,
            fn (DeliverNotificationChannelJob $job) => in_array(
                $job->deliveryLogId,
                [$customerEmailDeliveryId, $emailDeliveryId, $smsDeliveryId],
                true,
            ),
        );
    }

    public function test_assigning_a_rider_notifies_that_rider_on_every_private_channel(): void
    {
        Bus::fake();

        [, $customer] = $this->createCustomer();
        $vendor = $this->createVendor();
        $product = $this->createProduct($vendor, $this->createCategory(), price: 250);
        $this->createCart($customer, [[$product, 1]]);
        $order = collect(app(OrderService::class)->createFromCart($customer, $this->checkoutPayload()))->firstOrFail();
        $order->update(['order_status' => 'packed']);

        $role = Role::findOrCreate('Rider', 'web');
        $riderUser = User::factory()->create(['role_id' => $role->id, 'phone' => '0779999999']);
        $riderUser->assignRole($role);
        $rider = $riderUser->rider()->firstOrFail();
        $rider->update([
            'vehicle_type' => 'motorbike',
            'address' => '1 Rider Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'availability_status' => 'available',
            'verification_status' => 'verified',
        ]);

        $delivery = app(DeliveryService::class)->assignRider($order->refresh(), $rider->refresh());

        $this->assertSame('assigned', $delivery->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $riderUser->id,
            'type' => 'delivery_assigned',
        ]);
        $notification = Notification::query()
            ->where('user_id', $riderUser->id)
            ->where('type', 'delivery_assigned')
            ->firstOrFail();
        $this->assertSame($order->order_number, $notification->data['order_number']);
        $this->assertSame('Open delivery in DailyCart', $notification->data['email_action_label']);
        $this->assertSame($delivery->pickup_address, $notification->data['email_details']['Pickup address']);
        $this->assertSame($delivery->delivery_address, $notification->data['email_details']['Delivery address']);
        foreach (['database', 'email', 'sms', 'firebase'] as $channel) {
            $this->assertDatabaseHas('notification_delivery_logs', [
                'user_id' => $riderUser->id,
                'channel' => $channel,
            ]);
        }
    }

    public function test_rider_marking_an_order_delivered_queues_the_customer_invoice(): void
    {
        Mail::fake();
        Storage::fake('public');

        [, $customer] = $this->createCustomer();
        $vendor = $this->createVendor();
        $product = $this->createProduct($vendor, $this->createCategory(), price: 250);
        $this->createCart($customer, [[$product, 1]]);
        $order = collect(app(OrderService::class)->createFromCart($customer, $this->checkoutPayload()))->firstOrFail();
        $riderRole = Role::findOrCreate('Rider', 'web');
        $riderUser = User::factory()->create(['role_id' => $riderRole->id]);
        $riderUser->assignRole($riderRole);
        $rider = $riderUser->rider()->firstOrFail();
        $rider->update([
            'vehicle_type' => 'motorbike',
            'address' => '1 Rider Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'availability_status' => 'delivering',
            'verification_status' => 'verified',
        ]);
        $rider = $rider->refresh();
        $delivery = $order->delivery;
        $delivery->update([
            'rider_id' => $rider->id,
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        app(DeliveryService::class)->markOnTheWay($delivery->refresh());

        $this->assertSame('out_for_delivery', $order->refresh()->order_status);
        Mail::assertNotQueued(OrderInvoiceMail::class);

        app(DeliveryService::class)->markDelivered(
            $delivery->refresh(),
            UploadedFile::fake()->create('delivery-proof.jpg', 100, 'image/jpeg'),
        );

        $this->assertSame('delivered', $order->refresh()->order_status);
        $invoiceNotification = $customer->user->notifications()
            ->where('type', 'order_invoice')
            ->where('order_id', $order->id)
            ->firstOrFail();
        $invoiceEmail = NotificationDeliveryLog::query()
            ->where('notification_id', $invoiceNotification->id)
            ->where('channel', 'email')
            ->firstOrFail();

        app()->call([new DeliverNotificationChannelJob($invoiceEmail->id), 'handle']);

        Mail::assertSent(
            OrderInvoiceMail::class,
            fn (OrderInvoiceMail $mail) => $mail->order->is($order)
                && $mail->hasTo($customer->user->email),
        );
        $this->assertDatabaseHas('notification_delivery_logs', [
            'id' => $invoiceEmail->id,
            'status' => 'sent',
        ]);
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

        $adminRole = Role::findOrCreate('Super Admin', 'web');
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->put(route('super-admin.delivery.service-charge.update'), ['service_charge_rate_percent' => 7.5])
            ->assertSessionHasNoErrors();

        [, $customer] = $this->createCustomer();
        $product = $this->createProduct($this->createVendor(), $this->createCategory(), price: 1000);
        $this->createCart($customer, [[$product, 1]]);
        $order = collect(app(OrderService::class)->createFromCart($customer, $this->checkoutPayload()))->firstOrFail();
        $payment = $order->payment()->firstOrFail();

        $this->assertSame(75.0, (float) $order->service_charge);
        $this->assertSame(75.0, (float) $payment->service_charge);

        $this->actingAs($admin)
            ->put(route('super-admin.delivery.service-charge.update'), ['service_charge_rate_percent' => 10])
            ->assertSessionHasNoErrors();

        app(PaymentService::class)->syncPendingOrderAmounts($payment);

        $this->assertSame(75.0, (float) $order->refresh()->service_charge);
        $this->assertSame(75.0, (float) $payment->refresh()->service_charge);
    }

    public function test_only_super_admin_can_manage_delivery_pricing_rules(): void
    {
        $route = RouteFacade::getRoutes()->getByName('super-admin.delivery.rules.store');

        $this->assertContains('role:Super Admin', $route->gatherMiddleware());

        $role = Role::findOrCreate('Super Admin', 'web');
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->post(route('super-admin.delivery.rules.store'), [])
            ->assertSessionHasErrors(['scope', 'base_fee', 'per_km_fee', 'minimum_order', 'priority', 'status']);

        foreach (['Admin', 'Customer', 'Vendor', 'Rider'] as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $user = User::factory()->create(['role_id' => $role->id]);
            $user->assignRole($role);

            $this->actingAs($user)
                ->post(route('super-admin.delivery.rules.store'), [])
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

    public function test_customer_can_add_multiple_products_to_cart_in_one_request(): void
    {
        [$user] = $this->createCustomer();
        $vendor = $this->createVendor();
        $category = $this->createCategory();
        $firstProduct = $this->createProduct($vendor, $category, price: 100);
        $secondProduct = $this->createProduct($vendor, $category, price: 200);

        Sanctum::actingAs($user, ['customer']);

        $this->postJson('/api/v1/cart/bulk', [
            'items' => [
                ['product_id' => $firstProduct->id, 'quantity' => 2],
                ['product_id' => $secondProduct->id, 'quantity' => 1],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Selected products added to cart successfully.')
            ->assertJsonCount(2, 'cart')
            ->assertJsonPath('totals.item_count', 3);
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
        $customer = $user->customer()->firstOrFail();
        $customer->update([
            'first_name' => 'Customer '.$sequence,
            'phone' => $user->phone,
            'status' => 'active',
            'wallet_balance' => $walletBalance,
        ]);

        return [$user, $customer->refresh()];
    }

    private function createVendor(): Vendor
    {
        $sequence = $this->nextSequence();
        $role = Role::findOrCreate('Vendor', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '075'.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT),
        ]);
        $user->assignRole($role);

        $vendor = $user->vendor()->firstOrFail();
        $vendor->update([
            'store_name' => 'Vendor '.$sequence,
            'phone' => $user->phone,
            'address' => '1 Vendor Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);

        return $vendor->refresh();
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
