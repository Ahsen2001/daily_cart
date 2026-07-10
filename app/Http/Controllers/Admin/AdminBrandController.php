<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminBrandController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create', ['brand' => new Brand]);
    }

    public function store(Request $request): RedirectResponse
    {
        Brand::create($this->validated($request));

        return redirect()->route('admin.brands.index')->with('status', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->validated($request, $brand));

        return redirect()->route('admin.brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->update(['status' => 'inactive']);

        return back()->with('status', 'Brand deactivated.');
    }

    private function validated(Request $request, ?Brand $brand = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $brand?->id);

        return $data;
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        $candidate = $base;
        $counter = 2;

        while (Brand::where('slug', $candidate)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $candidate = $base.'-'.$counter++;
        }

        return $candidate;
    }
}
