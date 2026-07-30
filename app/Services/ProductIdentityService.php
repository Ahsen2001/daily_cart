<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductIdentityService
{
    public function uniqueSlug(string $name, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $base = Str::limit($base, 240, '');
        $candidate = $base;
        $sequence = 2;

        while (Product::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->exists()) {
            $suffix = '-'.$sequence++;
            $candidate = Str::limit($base, 255 - strlen($suffix), '').$suffix;
        }

        return $candidate;
    }

    public function assignSku(Product $product): Product
    {
        if (filled($product->sku)) {
            return $product;
        }

        $product->forceFill([
            'sku' => $this->generatedSku($product),
        ])->saveQuietly();

        return $product->refresh();
    }

    public function generatedSku(Product $product): string
    {
        $category = $product->relationLoaded('category')
            ? $product->category
            : Category::find($product->category_id);
        $categoryCode = $this->categoryCode($category);

        return sprintf('%s-V%03d-%06d', $categoryCode, $product->vendor_id, $product->id);
    }

    private function categoryCode(?Category $category): string
    {
        $source = Str::upper((string) ($category?->slug ?: $category?->name ?: 'GEN'));
        $code = preg_replace('/[^A-Z0-9]/', '', $source) ?: 'GEN';

        return Str::padRight(Str::substr($code, 0, 3), 3, 'X');
    }
}
