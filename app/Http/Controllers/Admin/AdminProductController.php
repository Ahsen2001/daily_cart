<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['vendor.user', 'category'])
            ->withCount('variants')
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
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', [
            'product' => $product->load(['vendor.user', 'category', 'images', 'variants.inventory', 'inventory']),
        ]);
    }

    public function approve(Product $product): RedirectResponse
    {
        $product->update([
            'status' => $product->stock_quantity > 0 ? 'approved' : 'out_of_stock',
            'is_subscription_eligible' => $product->stock_quantity > 0,
        ]);

        return back()->with('status', 'Product approved.');
    }

    public function reject(Product $product): RedirectResponse
    {
        $product->update([
            'status' => 'rejected',
            'is_featured' => false,
        ]);

        return back()->with('status', 'Product rejected.');
    }

    public function feature(Product $product): RedirectResponse
    {
        $product->update([
            'is_featured' => ! $product->is_featured,
        ]);

        return back()->with('status', 'Featured status updated.');
    }

    public function status(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,inactive,out_of_stock'],
        ]);

        $product->update([
            'status' => $validated['status'],
            'is_featured' => $validated['status'] === 'approved' ? $product->is_featured : false,
        ]);

        return back()->with('status', 'Product status updated.');
    }
}
