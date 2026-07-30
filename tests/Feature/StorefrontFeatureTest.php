<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storefront_browsing_does_not_change_global_product_visibility(): void
    {
        $category = Category::create([
            'name' => 'Storefront Groceries',
            'slug' => 'storefront-groceries',
            'status' => 'active',
        ]);
        $user = User::factory()->create(['phone' => '0751234567']);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => 'Fresh Mart',
            'phone' => '0751234567',
            'address' => '1 Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
        $store = VendorProfile::create([
            'vendor_id' => $vendor->id,
            'slug' => 'fresh-mart',
            'description' => 'Fresh groceries delivered daily.',
            'delivery_estimate' => '30-45 minutes',
            'profile_status' => 'approved',
            'is_enabled' => true,
            'is_featured' => true,
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Fresh Rice',
            'slug' => 'fresh-rice',
            'base_price' => 100,
            'price' => 100,
            'unit' => 'item',
            'unit_type' => 'item',
            'stock_quantity' => 10,
            'status' => 'approved',
        ]);

        $this->get('/stores?search=Fresh')
            ->assertOk()
            ->assertSee('Fresh Mart');
        $this->get(route('stores.show', $store))
            ->assertOk()
            ->assertSee('Fresh Rice')
            ->assertSee('Store products');
        $this->get(route('products.show', ['product' => $product->slug]))
            ->assertOk()
            ->assertSee('Sold by:')
            ->assertSee('Fresh Mart');

        $store->update(['is_enabled' => false]);

        $this->get(route('stores.show', $store))->assertNotFound();
        $this->get('/products')
            ->assertOk()
            ->assertSee('Fresh Rice');
    }
}
