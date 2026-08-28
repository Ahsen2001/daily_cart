<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\AccountDeletionService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorApprovalController extends Controller
{
    public function index(): View
    {
        return view('admin.vendors.index', [
            'vendors' => Vendor::with('user')->latest()->paginate(15),
        ]);
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load(['user', 'storeProfile'])->loadCount(['products', 'orders']);

        return view('admin.vendors.show', [
            'vendor' => $vendor,
            'recentProducts' => $vendor->products()->with('category')->latest()->limit(5)->get(),
            'recentOrders' => $vendor->orders()->with('customer.user')->latest()->limit(5)->get(),
        ]);
    }

    public function approve(Vendor $vendor, NotificationService $notifications): RedirectResponse
    {
        $vendor->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $vendor->user()->update(['status' => 'active']);
        $vendor->storeProfile()->updateOrCreate([
            'vendor_id' => $vendor->id,
        ], [
            'slug' => Str::slug($vendor->store_name).'-'.$vendor->id,
            'delivery_estimate' => '30-60 minutes',
            'contact_phone' => $vendor->phone,
            'profile_status' => 'approved',
            'is_enabled' => true,
            'approved_at' => now(),
            'reviewed_by' => request()->user()?->id,
        ]);
        $notifications->sendOnce(
            $vendor->user,
            'Congratulations — your vendor account is verified',
            'Congratulations! Your DailyCart vendor account has been verified and approved. You can now sign in and start managing your store.',
            'vendor_account_approved',
            ['database', 'mail', 'push'],
            ['vendor_id' => $vendor->id, 'status' => 'approved'],
            '/vendor/profile',
            'vendor',
            'vendor-approved-'.$vendor->id,
        );

        return back()->with('status', 'Vendor approved successfully.');
    }

    public function reject(Request $request, Vendor $vendor, NotificationService $notifications): RedirectResponse
    {
        $vendor->update(['status' => 'rejected']);
        $vendor->user()->update(['status' => 'suspended']);
        $notifications->sendOnce(
            $vendor->user,
            'Vendor registration rejected',
            'Your DailyCart vendor registration was rejected. Contact support for more information.',
            'vendor_account_rejected',
            ['database', 'mail', 'push'],
            ['vendor_id' => $vendor->id, 'status' => 'rejected'],
            '/vendor/profile',
            'vendor',
            'vendor-rejected-'.$vendor->id,
        );

        return back()->with('status', 'Vendor rejected.');
    }

    public function destroy(Vendor $vendor, AccountDeletionService $accounts): RedirectResponse
    {
        $user = $vendor->user()->withTrashed()->first();

        if ($user) {
            $accounts->delete($user);
        } else {
            $accounts->deleteVendorProfile($vendor);
        }

        return redirect()->route('admin.vendors.index')->with('status', 'Vendor account deleted from DailyCart.');
    }
}
