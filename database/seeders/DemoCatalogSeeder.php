<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::query()
            ->where('status', 'approved')
            ->whereHas('user', fn ($user) => $user->where('email', 'uahsens2001@gmail.com'))
            ->firstOrFail();

        $category = Category::query()->where('slug', 'fruits')->firstOrFail();
        $creator = User::query()->where('email', 'uahsens2@gmail.com')->firstOrFail();

        $product = Product::withTrashed()->firstOrNew(['slug' => 'fresh-mangoes-1kg']);
        $product->fill([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Fresh Mangoes 1kg',
            'brand' => 'DailyCart Fresh',
            'description' => 'Fresh, hand-selected mangoes supplied by an approved DailyCart vendor.',
            'price' => 750,
            'discount_price' => null,
            'unit_type' => 'pack',
            'weight' => 1,
            'sku' => 'DC-MANGO-1KG',
            'stock_quantity' => 50,
            'created_by' => $vendor->user_id,
            'base_price' => 750,
            'sale_price' => null,
            'unit' => '1 kg',
            'status' => 'approved',
            'is_featured' => true,
        ]);
        $product->deleted_at = null;
        $product->save();

        Promotion::withTrashed()->updateOrCreate(
            ['title' => 'Fresh Mango Weekend Deal'],
            [
                'vendor_id' => $vendor->id,
                'description' => 'Save 20% on fresh mangoes while stocks last.',
                'promotion_type' => 'flash_sale',
                'target_type' => 'product',
                'target_id' => $product->id,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addYear(),
                'status' => 'active',
                'created_by' => $creator->id,
                'deleted_at' => null,
            ]
        );
    }
}
