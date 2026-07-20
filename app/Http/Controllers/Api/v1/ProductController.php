<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()->where('status', 'active')->orderBy('name')->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'available' => ['nullable', 'boolean'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:price_low_high,price_high_low,latest,highest_rated,most_sold'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Product::visibleToCustomers();

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (filled($validated['search'] ?? null)) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%')
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('brandRelation', fn ($brand) => $brand->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }
        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }
        if (($validated['available'] ?? false) === true) {
            $query->where('stock_quantity', '>', 0);
        }
        if (filled($validated['brand'] ?? null)) {
            $brand = $validated['brand'];
            $query->where(function ($builder) use ($brand) {
                $builder->where('brand', $brand)
                    ->orWhereHas('brandRelation', fn ($relation) => $relation->where('name', $brand));
            });
        }
        if (isset($validated['rating']) || ($validated['sort'] ?? null) === 'highest_rated') {
            $query->withAvg(['reviews as visible_reviews_avg_rating' => fn ($reviews) => $reviews->where('status', 'visible')], 'rating');
        }
        if (isset($validated['rating'])) {
            $query->having('visible_reviews_avg_rating', '>=', $validated['rating']);
        }

        if (isset($validated['sort'])) {
            switch ($validated['sort']) {
                case 'price_low_high':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high_low':
                    $query->orderBy('price', 'desc');
                    break;
                case 'latest':
                    $query->latest();
                    break;
                case 'highest_rated':
                    $query->orderByDesc('visible_reviews_avg_rating');
                    break;
                case 'most_sold':
                    $query->withSum('orderItems as sold_quantity', 'quantity')
                        ->orderByDesc('sold_quantity');
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(15);

        return response()->json([
            'products' => ProductResource::collection($products),
            'pagination' => [
                'total' => $products->total(),
                'count' => $products->count(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless(
            Product::visibleToCustomers()->whereKey($product->getKey())->exists(),
            404
        );

        return response()->json([
            'product' => new ProductResource($product->load([
                'category',
                'vendor.user',
                'images',
                'variants.inventory',
                'reviews' => fn ($reviews) => $reviews->where('status', 'visible')->latest(),
                'reviews.customer.user',
            ])),
        ]);
    }
}
