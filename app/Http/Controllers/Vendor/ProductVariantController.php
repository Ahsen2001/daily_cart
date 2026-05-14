<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $product->variants()->create([
            'name' => $request->name,
            'price' => $request->price ?: ($product->discount_price ?? $product->price),
            'sku' => $request->sku,
            'status' => 'active',
        ]);

        return back()->with('status', 'Variant added.');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return back()->with('status', 'Variant deleted.');
    }
}
