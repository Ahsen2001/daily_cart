<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\ExternalEmailService;
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

    public function approve(Vendor $vendor, ExternalEmailService $emails): RedirectResponse
    {
        $vendor->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $vendor->user()->update(['status' => 'active']);
        $emails->approval(
            $vendor->user,
            'Congratulations — your vendor account is verified',
            'Congratulations! Your DailyCart vendor account has been verified and approved. You can now sign in and start managing your store.',
        );

        return back()->with('status', 'Vendor approved successfully.');
    }

    public function reject(Request $request, Vendor $vendor, ExternalEmailService $emails): RedirectResponse
    {
        $vendor->update(['status' => 'rejected']);
        $vendor->user()->update(['status' => 'suspended']);
        $emails->approval($vendor->user, 'Vendor rejected', 'Your DailyCart vendor registration was rejected.');

        return back()->with('status', 'Vendor rejected.');
    }
}
