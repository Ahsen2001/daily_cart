<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorGroceryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = Vendor::query()->where('status', 'approved')->firstOrFail();
        $category = Category::query()->where('status', 'active')
            ->where(fn ($query) => $query->where('slug', 'grocery')->orWhereRaw('LOWER(name) = ?', ['grocery']))
            ->first() ?? Category::query()->where('status', 'active')->firstOrFail();

        $products = [
            ['name' => 'Kekulu Rice 5kg', 'price' => 1250, 'unit' => '5 kg bag', 'stock' => 60],
            ['name' => 'Samba Rice 5kg', 'price' => 1450, 'unit' => '5 kg bag', 'stock' => 55],
            ['name' => 'Wheat Flour 1kg', 'price' => 260, 'unit' => '1 kg pack', 'stock' => 80],
            ['name' => 'White Sugar 1kg', 'price' => 290, 'unit' => '1 kg pack', 'stock' => 90],
            ['name' => 'Dhal Mysore 500g', 'price' => 340, 'unit' => '500 g pack', 'stock' => 70],
            ['name' => 'Coconut Oil 1L', 'price' => 790, 'unit' => '1 litre bottle', 'stock' => 45],
            ['name' => 'Ceylon Tea 200g', 'price' => 520, 'unit' => '200 g pack', 'stock' => 50],
            ['name' => 'Full Cream Milk Powder 400g', 'price' => 1180, 'unit' => '400 g pack', 'stock' => 40],
            ['name' => 'Iodised Salt 1kg', 'price' => 180, 'unit' => '1 kg pack', 'stock' => 100],
            ['name' => 'Red Chilli Powder 100g', 'price' => 230, 'unit' => '100 g pack', 'stock' => 65],
        ];

        foreach ($products as $item) {
            $slug = Str::slug($item['name']);
            $product = Product::withTrashed()->firstOrNew([
                'vendor_id' => $vendor->id,
                'slug' => $slug,
            ]);
            $product->fill([
                'category_id' => $category->id,
                'name' => $item['name'],
                'description' => 'Quality grocery essential supplied by '.$vendor->store_name.'.',
                'price' => $item['price'],
                'base_price' => $item['price'],
                'sale_price' => null,
                'unit' => $item['unit'],
                'unit_type' => 'pack',
                'stock_quantity' => $item['stock'],
                'sku' => 'GROC-'.Str::upper(Str::slug($item['name'], '-')),
                'created_by' => $vendor->user_id,
                'status' => 'approved',
                'is_featured' => false,
            ]);
            $product->deleted_at = null;
            $product->save();

            $variant = ProductVariant::withTrashed()->firstOrNew(['sku' => $product->sku.'-STD']);
            $variant->fill([
                'product_id' => $product->id,
                'name' => 'Standard',
                'price' => $item['price'],
                'status' => 'active',
            ]);
            $variant->deleted_at = null;
            $variant->save();

            Inventory::query()->updateOrCreate(
                ['product_id' => $product->id, 'product_variant_id' => null],
                ['quantity' => $item['stock'], 'low_stock_threshold' => 10],
            );
            Inventory::query()->updateOrCreate(
                ['product_id' => $product->id, 'product_variant_id' => $variant->id],
                ['quantity' => $item['stock'], 'low_stock_threshold' => 10],
            );
        }
    }
}
