<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VendorPromotionController extends Controller
{
    public function index(Request $request): View
    {
        return view('vendor.promotions.index', [
            'promotions' => Promotion::where('vendor_id', $request->user()->vendor->id)->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('vendor.promotions.create', ['promotion' => new Promotion]);
    }

    public function store(StorePromotionRequest $request, PromotionService $promotions): RedirectResponse
    {
        $data = $this->vendorPromotionData($request);
        $promotion = $promotions->create($data, $request->user(), $request->user()->vendor->id, $request->file('banner_image'));

        return redirect()->route('vendor.promotions.edit', $promotion)->with('status', 'Promotion created.');
    }

    public function edit(Promotion $promotion): View
    {
        $this->authorize('update', $promotion);

        return view('vendor.promotions.edit', compact('promotion'));
    }

    public function update(StorePromotionRequest $request, Promotion $promotion, PromotionService $promotions): RedirectResponse
    {
        $this->authorize('update', $promotion);
        $promotions->update($promotion, $this->vendorPromotionData($request), $request->file('banner_image'));

        return back()->with('status', 'Promotion updated.');
    }

    private function vendorPromotionData(StorePromotionRequest $request): array
    {
        $data = $request->validated();

        if ($data['target_type'] !== 'product') {
            throw ValidationException::withMessages(['target_type' => 'Vendors can create promotions only for their own products.']);
        }

        $ownsProduct = Product::whereKey($data['target_id'])->where('vendor_id', $request->user()->vendor->id)->exists();

        if (! $ownsProduct) {
            throw ValidationException::withMessages(['target_id' => 'Vendors can create promotions only for their own products.']);
        }

        return $data;
    }
}
