<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorRefundController extends Controller
{
    public function index(Request $request): View
    {
        $refunds = Refund::query()
            ->with(['order.customer.user', 'payment'])
            ->whereHas('order', fn ($query) => $query->where('vendor_id', $request->user()->vendor->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('vendor.refunds.index', compact('refunds'));
    }
}
