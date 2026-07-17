<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCoreFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_registration_creates_a_complete_customer_account(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'API Customer',
            'email' => 'api-customer@example.com',
            'phone' => '0771000001',
            'password' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'Customer')
            ->assertJsonStructure(['token', 'user']);

        $user = User::where('email', 'api-customer@example.com')->firstOrFail();

        $this->assertNotNull($user->role_id);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'first_name' => 'API Customer',
            'phone' => '0771000001',
        ]);
    }

    public function test_cart_service_rejects_a_variant_from_another_product(): void
    {
        $product = new Product([
            'status' => 'approved',
            'stock_quantity' => 10,
        ]);
        $product->id = 10;
        $product->setRelation('category', new Category(['status' => 'active']));

        $variant = new ProductVariant([
            'product_id' => 11,
            'price' => 1,
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);

        app(CartService::class)->ensureProductCanBeOrdered($product, 1, $variant);
    }

    public function test_api_checkout_uses_the_shared_payment_and_schedule_validation(): void
    {
        [$user] = $this->createCustomer('checkout');
        Sanctum::actingAs($user, ['customer']);

        $payload = [
            'delivery_address' => '1 Test Street, Colombo',
            'payment_method' => 'online_payment',
            'scheduled_delivery_at' => now()->addHour()->toISOString(),
        ];

        $this->postJson('/api/v1/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');

        $payload['payment_method'] = 'card';

        $this->postJson('/api/v1/orders', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');
    }

    public function test_quote_and_order_creation_share_per_vendor_delivery_pricing(): void
    {
        [, $customer] = $this->createCustomer('pricing');
        $category = Category::create([
            'name' => 'Pricing Category',
            'slug' => 'pricing-category',
            'status' => 'active',
        ]);
        $firstVendor = $this->createVendor('first');
        $secondVendor = $this->createVendor('second');
        $firstProduct = $this->createProduct($firstVendor, $category, 'First Product');
        $secondProduct = $this->createProduct($secondVendor, $category, 'Second Product');
        $cart = Cart::create(['customer_id' => $customer->id, 'status' => 'active']);

        $cart->items()->create([
            'product_id' => $firstProduct->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);
        $cart->items()->create([
            'product_id' => $secondProduct->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $quote = app(OrderService::class)->quote($cart, null, $customer);

        $this->assertSame(200.0, $quote['subtotal']);
        $this->assertSame(500.0, $quote['delivery_fee']);
        $this->assertSame(4.0, $quote['service_charge']);
        $this->assertSame(704.0, $quote['grand_total']);

        $orders = app(OrderService::class)->createFromCart($customer, [
            'delivery_address' => '1 Test Street, Colombo',
            'payment_method' => 'cash_on_delivery',
            'scheduled_delivery_at' => now()->addHour(),
            'client_current_at' => '2000-01-01 00:00:00',
        ]);

        $this->assertCount(2, $orders);
        $this->assertSame($quote['delivery_fee'], round((float) collect($orders)->sum('delivery_fee'), 2));
        $this->assertSame($quote['grand_total'], round((float) collect($orders)->sum('total_amount'), 2));
        $this->assertTrue(collect($orders)->every(fn ($order) => $order->placed_at->isToday()));
    }

    public function test_public_product_details_hide_unpublished_products(): void
    {
        $category = Category::create([
            'name' => 'Public Category',
            'slug' => 'public-category',
            'status' => 'active',
        ]);
        $vendor = $this->createVendor('public');
        $visibleProduct = $this->createProduct($vendor, $category, 'Visible Product');
        $hiddenProduct = $this->createProduct($vendor, $category, 'Hidden Product', 'rejected');

        $this->getJson('/api/v1/products/'.$visibleProduct->id)->assertOk();
        $this->getJson('/api/v1/products/'.$hiddenProduct->id)->assertNotFound();
    }

    /** @return array{User, Customer} */
    private function createCustomer(string $suffix): array
    {
        $role = Role::findOrCreate('Customer', 'web');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '076'.str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
        ]);
        $user->assignRole($role);

        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => ucfirst($suffix),
            'phone' => $user->phone,
            'status' => 'active',
        ]);

        return [$user, $customer];
    }

    private function createVendor(string $suffix): Vendor
    {
        $user = User::factory()->create([
            'phone' => '075'.str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
        ]);

        return Vendor::create([
            'user_id' => $user->id,
            'store_name' => ucfirst($suffix).' Store',
            'phone' => $user->phone,
            'address' => '1 Vendor Street',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
    }

    private function createProduct(Vendor $vendor, Category $category, string $name, string $status = 'approved'): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'base_price' => 100,
            'price' => 100,
            'unit' => 'item',
            'unit_type' => 'item',
            'stock_quantity' => 10,
            'status' => $status,
        ]);
    }
}
