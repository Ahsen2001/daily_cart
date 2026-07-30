<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ProductIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_and_sku_are_generated_uniquely_for_vendor_products(): void
    {
        $category = Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits',
            'status' => 'active',
        ]);
        $vendor = $this->vendor('Fresh Mart');
        $identity = app(ProductIdentityService::class);

        $first = $this->product($vendor, $category, 'Fresh Red Apple', $identity->uniqueSlug('Fresh Red Apple'));
        $first = $identity->assignSku($first);
        $second = $this->product($vendor, $category, 'Fresh Red Apple', $identity->uniqueSlug('Fresh Red Apple'));
        $second = $identity->assignSku($second);

        $this->assertSame('fresh-red-apple', $first->slug);
        $this->assertSame('fresh-red-apple-2', $second->slug);
        $this->assertMatchesRegularExpression('/^FRU-V\d{3}-\d{6}$/', $first->sku);
        $this->assertNotSame($first->sku, $second->sku);
        $this->assertDatabaseCount('products', 2);
    }

    private function vendor(string $storeName): Vendor
    {
        $user = User::factory()->create();

        return Vendor::create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'phone' => '075'.str_pad((string) $user->id, 7, '0', STR_PAD_LEFT),
            'address' => '1 Market Road',
            'city' => 'Colombo',
            'district' => 'Colombo',
            'status' => 'approved',
        ]);
    }

    private function product(Vendor $vendor, Category $category, string $name, string $slug): Product
    {
        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'base_price' => 100,
            'price' => 100,
            'unit' => 'item',
            'unit_type' => 'item',
            'stock_quantity' => 10,
            'status' => 'pending',
        ]);
    }
}
