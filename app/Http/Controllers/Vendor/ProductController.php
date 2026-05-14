<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;

        $products = Product::query()
            ->with(['category', 'inventory'])
            ->where('vendor_id', $vendor->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('brand', 'like', '%'.$request->search.'%')
                        ->orWhere('sku', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('vendor.products.index', [
            'products' => $products,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('vendor.products.create', [
            'categories' => Category::active()->orderBy('name')->get(),
            'variantExamples' => ['500g', '1kg', '2kg', '5kg', 'Small', 'Medium', 'Large'],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;

        $product = DB::transaction(function () use ($request, $vendor) {
            $data = $this->productData($request->validated());
            $data['vendor_id'] = $vendor->id;
            $data['created_by'] = $request->user()->id;
            $data['status'] = $data['stock_quantity'] > 0 ? 'pending' : 'out_of_stock';
            $data['slug'] = $this->uniqueSlug($data['slug'], $vendor->id);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($data);
            $this->syncInventory($product, (int) $data['stock_quantity']);
            $this->storeImages($product, $request);
            $this->storeVariants($product, $request->input('variants', []));

            return $product;
        });

        return redirect()->route('vendor.products.show', $product)->with('status', 'Product submitted for approval.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        return view('vendor.products.show', [
            'product' => $product->load(['category', 'images', 'variants', 'inventory']),
        ]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('vendor.products.edit', [
            'product' => $product->load(['images', 'variants', 'inventory']),
            'categories' => Category::active()->orderBy('name')->get(),
            'variantExamples' => ['500g', '1kg', '2kg', '5kg', 'Small', 'Medium', 'Large'],
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $data = $this->productData($request->validated());
            $data['status'] = $data['stock_quantity'] > 0 ? 'pending' : 'out_of_stock';
            $data['is_featured'] = false;
            $data['slug'] = $this->uniqueSlug($data['slug'], $product->vendor_id, $product->id);

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }

                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
            $this->syncInventory($product, (int) $data['stock_quantity']);
            $this->storeImages($product, $request);
            $this->storeVariants($product, $request->input('variants', []));
        });

        return redirect()->route('vendor.products.show', $product)->with('status', 'Product updated and sent for approval.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('vendor.products.index')->with('status', 'Product deleted.');
    }

    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product->update([
            'stock_quantity' => $validated['stock_quantity'],
            'status' => $validated['stock_quantity'] > 0 ? $product->status : 'out_of_stock',
        ]);

        $this->syncInventory($product, (int) $validated['stock_quantity']);

        return back()->with('status', 'Stock updated.');
    }

    private function productData(array $validated): array
    {
        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        return [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'brand' => $validated['brand'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'unit_type' => $validated['unit_type'],
            'weight' => $validated['weight'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'stock_quantity' => $validated['stock_quantity'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'base_price' => $validated['price'],
            'sale_price' => $validated['discount_price'] ?? null,
            'unit' => $validated['unit_type'],
        ];
    }

    private function syncInventory(Product $product, int $quantity): void
    {
        $product->inventory()->updateOrCreate(
            ['product_variant_id' => null],
            ['quantity' => $quantity, 'low_stock_threshold' => 5]
        );
    }

    private function uniqueSlug(string $slug, int $vendorId, ?int $ignoreProductId = null): string
    {
        $base = Str::slug($slug);
        $candidate = $base;
        $counter = 2;

        while (
            Product::where('vendor_id', $vendorId)
                ->where('slug', $candidate)
                ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
                ->exists()
        ) {
            $candidate = $base.'-'.$counter++;
        }

        return $candidate;
    }

    private function storeImages(Product $product, Request $request): void
    {
        foreach ($request->file('images', []) as $index => $image) {
            $product->images()->create([
                'image_path' => $image->store('products/gallery', 'public'),
                'alt_text' => $product->name,
                'sort_order' => $product->images()->count() + $index,
                'is_primary' => false,
            ]);
        }
    }

    private function storeVariants(Product $product, array $variants): void
    {
        collect($variants)
            ->filter()
            ->unique()
            ->each(fn (string $variant) => $product->variants()->firstOrCreate(
                ['name' => $variant],
                ['price' => $product->discount_price ?? $product->price, 'status' => 'active']
            ));
    }
}
