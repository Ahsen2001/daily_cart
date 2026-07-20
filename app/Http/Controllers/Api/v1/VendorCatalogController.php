<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'low_stock' => ['nullable', 'boolean'],
        ]);
        $products = $request->user()->vendor->products()
            ->with($this->relations())
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"));
            })
            ->when($validated['low_stock'] ?? false, fn ($query) => $query->where('stock_quantity', '<=', 5))
            ->latest()
            ->paginate(15);

        return response()->json([
            'products' => collect($products->items())->map(fn (Product $product) => $this->payload($product)),
            'pagination' => $this->pagination($products),
        ]);
    }

    public function store(Request $request, NotificationService $notifications): JsonResponse
    {
        $validated = $this->validateProduct($request);
        $vendor = $request->user()->vendor;

        $product = DB::transaction(function () use ($validated, $vendor, $request) {
            $product = Product::create([
                ...$this->productData($validated),
                'vendor_id' => $vendor->id,
                'created_by' => $request->user()->id,
                'slug' => $this->uniqueSlug($validated['slug'] ?? $validated['name'], $vendor->id),
                'status' => (int) $validated['stock_quantity'] > 0 ? 'pending' : 'out_of_stock',
            ]);
            $this->syncBaseInventory($product, (int) $validated['stock_quantity']);

            return $product;
        });

        $notifications->notifyAdmins(
            'Product approval required',
            ($vendor->store_name ?: $request->user()->name).' submitted "'.$product->name.'" for approval.',
            'product_submitted_for_approval',
            ['database', 'mail'],
        );

        return response()->json([
            'message' => 'Product submitted for approval.',
            'product' => $this->payload($product->load($this->relations())),
        ], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwned($request, $product);

        return response()->json([
            'product' => $this->payload($product->load($this->relations())),
        ]);
    }

    public function update(
        Request $request,
        Product $product,
        NotificationService $notifications
    ): JsonResponse {
        $this->ensureOwned($request, $product);
        $validated = $this->validateProduct($request, $product);
        DB::transaction(function () use ($product, $validated) {
            $product->update([
                ...$this->productData($validated),
                'slug' => $this->uniqueSlug(
                    $validated['slug'] ?? $validated['name'],
                    $product->vendor_id,
                    $product->id,
                ),
                'status' => (int) $validated['stock_quantity'] > 0 ? 'pending' : 'out_of_stock',
                'is_featured' => false,
            ]);
            $this->syncBaseInventory($product, (int) $validated['stock_quantity']);
        });
        $notifications->notifyAdmins(
            'Product approval required',
            $request->user()->vendor->store_name.' updated "'.$product->name.'".',
            'product_submitted_for_approval',
            ['database', 'mail'],
        );

        return response()->json([
            'message' => 'Product updated and sent for approval.',
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwned($request, $product);
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    public function uploadImages(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwned($request, $product);
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        foreach ($validated['images'] as $index => $image) {
            $product->images()->create([
                'image_path' => $image->store('products/gallery', 'public'),
                'alt_text' => $product->name,
                'sort_order' => $product->images()->count() + $index,
                'is_primary' => ! $product->image && $product->images()->count() === 0,
            ]);
        }
        if (! $product->image && ($primary = $product->images()->orderBy('sort_order')->first())) {
            $product->update(['image' => $primary->image_path]);
        }

        return response()->json([
            'message' => 'Product images uploaded.',
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ]);
    }

    public function destroyImage(
        Request $request,
        Product $product,
        ProductImage $image
    ): JsonResponse {
        $this->ensureOwned($request, $product);
        abort_unless($image->product_id === $product->id, 404);
        $wasPrimary = $product->image === $image->image_path;
        $image->delete();
        if ($wasPrimary) {
            $product->update(['image' => $product->images()->orderBy('sort_order')->value('image_path')]);
        }

        return response()->json([
            'message' => 'Product image deleted.',
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ]);
    }

    public function storeVariant(Request $request, Product $product): JsonResponse
    {
        $this->ensureOwned($request, $product);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variants,sku'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);
        $variant = DB::transaction(function () use ($product, $validated) {
            $variant = $product->variants()->create([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'price' => $validated['price'] ?? $product->discount_price ?? $product->price,
                'status' => 'active',
            ]);
            $variant->inventory()->create([
                'product_id' => $product->id,
                'quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            ]);

            return $variant;
        });
        $this->markPending($product);

        return response()->json([
            'message' => 'Variant added and product sent for approval.',
            'variant' => $this->variantPayload($variant->load('inventory')),
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ], 201);
    }

    public function updateVariant(
        Request $request,
        Product $product,
        ProductVariant $variant
    ): JsonResponse {
        $this->ensureOwnedVariant($request, $product, $variant);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);
        DB::transaction(function () use ($product, $variant, $validated) {
            $variant->update([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'price' => $validated['price'],
            ]);
            $variant->inventory()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => $validated['stock_quantity'],
                    'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
                ],
            );
        });
        $this->markPending($product);

        return response()->json([
            'message' => 'Variant updated.',
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ]);
    }

    public function destroyVariant(
        Request $request,
        Product $product,
        ProductVariant $variant
    ): JsonResponse {
        $this->ensureOwnedVariant($request, $product, $variant);
        $variant->delete();
        $this->markPending($product);

        return response()->json([
            'message' => 'Variant deleted.',
            'product' => $this->payload($product->refresh()->load($this->relations())),
        ]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $products = $request->user()->vendor->products()
            ->with($this->relations())
            ->orderBy('stock_quantity')
            ->get();

        return response()->json([
            'inventory' => $products->map(fn (Product $product) => $this->payload($product)),
            'low_stock_count' => $products->where('stock_quantity', '<=', 5)->count(),
        ]);
    }

    public function updateInventory(
        Request $request,
        Product $product,
        NotificationService $notifications
    ): JsonResponse {
        $this->ensureOwned($request, $product);
        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        ]);
        $product->update([
            'stock_quantity' => $validated['stock_quantity'],
            'expiry_date' => $validated['expiry_date'] ?? $product->expiry_date,
            'status' => $validated['stock_quantity'] > 0
                ? ($product->status === 'out_of_stock' ? 'pending' : $product->status)
                : 'out_of_stock',
        ]);
        $product->inventory()->updateOrCreate(
            ['product_variant_id' => null],
            [
                'quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            ],
        );
        $notifications->lowStockAlert($product->refresh());

        return response()->json([
            'message' => 'Inventory updated.',
            'product' => $this->payload($product->load($this->relations())),
        ]);
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'unit_type' => ['required', 'string', 'max:50'],
            'weight' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'is_subscription_eligible' => ['nullable', 'boolean'],
        ]);
    }

    private function productData(array $data): array
    {
        return [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'brand' => $data['brand'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? null,
            'unit_type' => $data['unit_type'],
            'unit' => $data['unit_type'],
            'weight' => $data['weight'] ?? null,
            'sku' => $data['sku'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'base_price' => $data['price'],
            'sale_price' => $data['discount_price'] ?? null,
            'is_subscription_eligible' => $data['is_subscription_eligible'] ?? false,
        ];
    }

    private function payload(Product $product): array
    {
        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'name' => $product->name,
            'brand' => $product->brand,
            'description' => $product->description,
            'price' => (float) $product->price,
            'discount_price' => $product->discount_price !== null ? (float) $product->discount_price : null,
            'unit_type' => $product->unit_type,
            'weight' => $product->weight,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'stock_quantity' => (int) $product->stock_quantity,
            'low_stock_threshold' => (int) ($product->inventory?->low_stock_threshold ?? 5),
            'expiry_date' => $product->expiry_date,
            'status' => $product->status,
            'rejection_reason' => $product->rejection_reason,
            'is_subscription_eligible' => (bool) $product->is_subscription_eligible,
            'image' => $product->image ? url('storage/'.$product->image) : null,
            'images' => $product->images->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => url('storage/'.$image->image_path),
                'is_primary' => $image->is_primary,
            ]),
            'variants' => $product->variants->map(fn (ProductVariant $variant) => $this->variantPayload($variant)),
        ];
    }

    private function variantPayload(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'name' => $variant->name,
            'value' => $variant->name,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'stock_quantity' => (int) ($variant->inventory?->quantity ?? 0),
            'low_stock_threshold' => (int) ($variant->inventory?->low_stock_threshold ?? 5),
            'status' => $variant->status,
        ];
    }

    private function relations(): array
    {
        return ['category', 'images', 'inventory', 'variants.inventory'];
    }

    private function syncBaseInventory(Product $product, int $quantity): void
    {
        $product->inventory()->updateOrCreate(
            ['product_variant_id' => null],
            ['quantity' => $quantity, 'low_stock_threshold' => 5],
        );
    }

    private function markPending(Product $product): void
    {
        if ($product->status === 'approved') {
            $product->update(['status' => 'pending', 'is_featured' => false]);
        }
    }

    private function uniqueSlug(string $value, int $vendorId, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $candidate = $base;
        $counter = 2;
        while (Product::where('vendor_id', $vendorId)
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$counter++;
        }

        return $candidate;
    }

    private function ensureOwned(Request $request, Product $product): void
    {
        abort_unless($product->vendor_id === $request->user()->vendor?->id, 403);
    }

    private function ensureOwnedVariant(
        Request $request,
        Product $product,
        ProductVariant $variant
    ): void {
        $this->ensureOwned($request, $product);
        abort_unless($variant->product_id === $product->id, 404);
    }

    private function pagination($paginator): array
    {
        return [
            'total' => $paginator->total(),
            'count' => $paginator->count(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
        ];
    }
}
