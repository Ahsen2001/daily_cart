<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\StoreBanner;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('vendor.store.edit', ['profile' => $this->profileFor($request)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $data = $request->validate([
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('vendor_profiles', 'slug')->ignore($profile->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'opening_hours' => ['nullable', 'string', 'max:1000'],
            'delivery_estimate' => ['nullable', 'string', 'max:100'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'cover_image' => ['nullable', 'image', 'max:6144'],
        ]);

        $profileData = collect($data)->except(['opening_hours', 'logo', 'cover_image'])->all();
        $profileData['slug'] = $data['slug'] ?: Str::slug($request->user()->vendor->store_name).'-'.$request->user()->vendor->id;
        $profileData['opening_hours'] = filled($data['opening_hours'] ?? null) ? ['summary' => $data['opening_hours']] : null;

        foreach (['logo' => 'logo_path', 'cover_image' => 'cover_image_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                if ($profile->{$column}) {
                    Storage::disk('public')->delete($profile->{$column});
                }
                $profileData[$column] = $request->file($input)->store('stores/'.$profile->vendor_id, 'public');
            }
        }

        $profile->update($profileData);

        return back()->with('status', 'Store profile saved.');
    }

    public function banners(Request $request): View
    {
        return view('vendor.store.banners', ['profile' => $this->profileFor($request)->load('banners')]);
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['required', 'image', 'max:6144'],
        ]);

        $profile->banners()->create([
            ...collect($data)->except('image')->all(),
            'image_path' => $request->file('image')->store('stores/'.$profile->vendor_id.'/banners', 'public'),
            'sort_order' => (int) ($profile->banners()->max('sort_order') ?? -1) + 1,
        ]);

        return back()->with('status', 'Store banner uploaded.');
    }

    public function destroyBanner(Request $request, StoreBanner $banner): RedirectResponse
    {
        $profile = $this->profileFor($request);
        abort_unless($banner->vendor_profile_id === $profile->id, 404);

        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        return back()->with('status', 'Store banner removed.');
    }

    public function analytics(Request $request): View
    {
        $profile = $this->profileFor($request);
        $vendor = $profile->vendor;

        return view('vendor.store.analytics', [
            'profile' => $profile->loadCount('followers'),
            'metrics' => [
                'approved_products' => $vendor->products()->visibleToCustomers()->count(),
                'followers' => $profile->followers()->count(),
                'orders' => $vendor->orders()->count(),
                'revenue' => $vendor->orders()->whereNotIn('order_status', ['cancelled'])->sum('total_amount'),
                'average_rating' => round((float) $vendor->reviews()->where('status', 'visible')->avg('rating'), 1),
            ],
        ]);
    }

    private function profileFor(Request $request): VendorProfile
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        return $vendor->storeProfile()->firstOrCreate([
            'slug' => Str::slug($vendor->store_name).'-'.$vendor->id,
        ], [
            'delivery_estimate' => '30-60 minutes',
            'contact_phone' => $vendor->phone,
            'profile_status' => 'pending',
        ]);
    }
}
