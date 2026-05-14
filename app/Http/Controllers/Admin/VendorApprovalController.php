<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.vendors.index', [
            'vendors' => Vendor::with('user')->latest()->paginate(15),
        ]);
    }

    public function approve(Vendor $vendor): RedirectResponse
    {
        $vendor->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $vendor->user()->update(['status' => 'active']);

        return back()->with('status', 'Vendor approved successfully.');
    }

    public function reject(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update(['status' => 'rejected']);
        $vendor->user()->update(['status' => 'suspended']);

        return back()->with('status', 'Vendor rejected.');
    }
}
