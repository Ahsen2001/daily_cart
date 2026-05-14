<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductBrowseController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->visibleToCustomers()
            ->with(['category', 'vendor', 'images'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('brand', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('customer.products.index', [
            'products' => $products,
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'approved' && $product->category?->status === 'active', 404);

        return view('customer.products.show', [
            'product' => $product->load(['category', 'vendor', 'images', 'variants']),
        ]);
    }
}
