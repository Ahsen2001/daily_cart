<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Models\Coupon;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::with('vendor')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon(),
            'vendors' => Vendor::orderBy('store_name', 'asc')->get(),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::create($this->data($request->validated()));

        return redirect()->route('admin.coupons.edit', $coupon)->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', ['coupon' => $coupon, 'vendors' => Vendor::orderBy('store_name', 'asc')->get()]);
    }

    public function update(StoreCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->data($request->validated()));

        return back()->with('status', 'Coupon updated.');
    }

    private function data(array $data): array
    {
        $data['type'] = $data['discount_type'] === 'percentage' ? 'percentage' : 'fixed';
        $data['value'] = $data['discount_value'];
        $data['max_discount_amount'] = $data['maximum_discount_amount'] ?? null;

        return $data;
    }
}
