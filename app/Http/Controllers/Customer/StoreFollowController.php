<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreFollowController extends Controller
{
    public function store(Request $request, VendorProfile $store): RedirectResponse
    {
        abort_unless(VendorProfile::publiclyVisible()->whereKey($store->id)->exists(), 404);

        $store->followers()->syncWithoutDetaching([$request->user()->customer->id]);

        return back()->with('status', 'Store added to your favorites.');
    }

    public function destroy(Request $request, VendorProfile $store): RedirectResponse
    {
        $store->followers()->detach($request->user()->customer->id);

        return back()->with('status', 'Store removed from your favorites.');
    }
}
