<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorCouponController extends Controller
{
    public function index(Request $request): View
    {
        return view('vendor.coupons.index', [
            'coupons' => Coupon::where('vendor_id', $request->user()->vendor->id)->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('vendor.coupons.create', ['coupon' => new Coupon]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $data = $this->data($request->validated(), $request->user()->vendor->id);
        $coupon = Coupon::create($data);

        return redirect()->route('vendor.coupons.edit', $coupon)->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        $this->authorize('update', $coupon);

        return view('vendor.coupons.edit', compact('coupon'));
    }

    public function update(StoreCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->authorize('update', $coupon);
        $coupon->update($this->data($request->validated(), $request->user()->vendor->id));

        return back()->with('status', 'Coupon updated.');
    }

    private function data(array $data, int $vendorId): array
    {
        $data['vendor_id'] = $vendorId;
        $data['type'] = $data['discount_type'] === 'percentage' ? 'percentage' : 'fixed';
        $data['value'] = $data['discount_value'];
        $data['max_discount_amount'] = $data['maximum_discount_amount'] ?? null;

        return $data;
    }
}
