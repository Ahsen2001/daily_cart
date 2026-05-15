<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('customer.coupons.index', [
            'coupons' => Coupon::query()
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                ->latest()
                ->paginate(15),
        ]);
    }
}
