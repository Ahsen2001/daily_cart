<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageOffersTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_vendor_product_offer_appears_on_homepage(): void
    {
        [$user, $vendor, $product] = $this->approvedProduct();

        $vendorPromotion = Promotion::create([
            'vendor_id' => $vendor->id,
            'title' => 'Fresh Mango Weekend Deal',
            'description' => 'Save on fresh mangoes today.',
            'promotion_type' => 'flash_sale',
            'target_type' => 'product',
            'target_id' => $product->id,
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $admin = User::factory()->create();
        $adminPromotion = Promotion::create([
            'vendor_id' => null,
            'title' => 'DailyCart Admin Special',
            'description' => 'A platform offer announced by DailyCart.',
            'promotion_type' => 'featured_offer',
            'target_type' => 'product',
            'target_id' => $product->id,
            'discount_type' => 'fixed_amount',
            'discount_value' => 150,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Offers Today')
            ->assertSee('Fresh Mango Weekend Deal')
            ->assertSee('20% OFF')
            ->assertSee('DailyCart Admin Special')
            ->assertSee('Rs. 150.00 OFF')
            ->assertSee($product->name)
            ->assertSee('href="'.route('products.show', ['product' => $product, 'promotion' => $vendorPromotion->id]).'"', false)
            ->assertSee('href="'.route('products.show', ['product' => $product, 'promotion' => $adminPromotion->id]).'"', false);

        $this->get('/offers')
            ->assertOk()
            ->assertSee('Offers Today')
            ->assertSee('Fresh Mango Weekend Deal')
            ->assertSee('DailyCart Admin Special')
            ->assertSee('href="'.route('products.show', ['product' => $product, 'promotion' => $vendorPromotion->id]).'"', false)
            ->assertSee('href="'.route('products.show', ['product' => $product, 'promotion' => $adminPromotion->id]).'"', false);
    }

    public function test_expired_or_unapproved_vendor_offers_are_hidden_from_homepage(): void
    {
        [$approvedUser, $approvedVendor, $approvedProduct] = $this->approvedProduct();

        Promotion::create([
            'vendor_id' => $approvedVendor->id,
            'title' => 'Expired Offer Should Be Hidden',
            'promotion_type' => 'flash_sale',
            'target_type' => 'product',
            'target_id' => $approvedProduct->id,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
            'status' => 'active',
            'created_by' => $approvedUser->id,
        ]);

        [$pendingUser, $pendingVendor, $pendingProduct] = $this->approvedProduct('pending');

        Promotion::create([
            'vendor_id' => $pendingVendor->id,
            'title' => 'Pending Vendor Offer Should Be Hidden',
            'promotion_type' => 'featured_offer',
            'target_type' => 'product',
            'target_id' => $pendingProduct->id,
            'discount_type' => 'fixed_amount',
            'discount_value' => 100,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'active',
            'created_by' => $pendingUser->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Expired Offer Should Be Hidden')
            ->assertDontSee('Pending Vendor Offer Should Be Hidden')
            ->assertDontSee('Offers Today');
    }

    public function test_logged_in_customer_sees_offers_and_offer_price_is_used_by_product_and_cart(): void
    {
        [$creator, $vendor, $product] = $this->approvedProduct();
        $product->update(['stock_quantity' => 10]);
        $promotion = Promotion::create([
            'vendor_id' => $vendor->id,
            'title' => 'Customer Dashboard Special',
            'promotion_type' => 'featured_offer',
            'target_type' => 'product',
            'target_id' => $product->id,
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => 'active',
            'created_by' => $creator->id,
        ]);

        $role = Role::findOrCreate('Customer', 'web');
        $customerUser = User::factory()->create(['role_id' => $role->id, 'phone' => '0771234567']);
        $customerUser->assignRole($role);
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'first_name' => 'Offer Customer',
            'phone' => $customerUser->phone,
            'status' => 'active',
        ]);

        $this->actingAs($customerUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Offers Today')
            ->assertSee('Customer Dashboard Special');

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Offers Today')
            ->assertSee('Customer Dashboard Special');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Customer Dashboard Special')
            ->assertSee('Rs. 600.00')
            ->assertSee('Rs. 750.00');

        $cartService = app(CartService::class);
        $item = $cartService->add($customer, $product->load(['category', 'vendor']), 1);

        $this->assertSame(600.0, (float) $item->unit_price);

        $promotion->update(['ends_at' => now()->subMinute()]);
        $totals = $cartService->totals($item->cart->load('items'));

        $this->assertSame(750.0, (float) $item->refresh()->unit_price);
        $this->assertSame(750.0, $totals['subtotal']);
    }

    private function approvedProduct(string $vendorStatus = 'approved'): array
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => fake()->unique()->company(),
            'phone' => fake()->unique()->phoneNumber(),
            'address' => fake()->address(),
            'city' => 'Batticaloa',
            'district' => 'Batticaloa',
            'status' => $vendorStatus,
            'approved_at' => $vendorStatus === 'approved' ? now() : null,
        ]);
        $category = Category::create([
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'price' => 750,
            'base_price' => 750,
            'unit_type' => 'item',
            'unit' => 'item',
            'stock_quantity' => 10,
            'status' => 'approved',
        ]);

        return [$user, $vendor, $product];
    }
}
