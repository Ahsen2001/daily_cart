<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Promotion;
use App\Models\Vendor;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', [
            'promotions' => Promotion::with('vendor')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.create', ['promotion' => new Promotion, 'vendors' => Vendor::orderBy('store_name')->get()]);
    }

    public function store(StorePromotionRequest $request, PromotionService $promotions): RedirectResponse
    {
        $promotion = $promotions->create($request->validated(), $request->user(), $request->vendor_id, $request->file('banner_image'));

        return redirect()->route('admin.promotions.edit', $promotion)->with('status', 'Promotion created.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', ['promotion' => $promotion, 'vendors' => Vendor::orderBy('store_name')->get()]);
    }

    public function update(StorePromotionRequest $request, Promotion $promotion, PromotionService $promotions): RedirectResponse
    {
        $promotions->update($promotion, $request->validated(), $request->file('banner_image'));

        return back()->with('status', 'Promotion updated.');
    }
}
