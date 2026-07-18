<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExternalEmailService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-vendor');
    }

    public function store(Request $request, ExternalEmailService $emails): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'store_name' => ['required', 'string', 'max:255'],
            'business_registration_no' => ['nullable', 'string', 'max:255', Rule::unique(Vendor::class, 'business_registration_no')->withoutTrashed()],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->withoutTrashed()],
            'phone' => ['required', 'string', 'max:30', Rule::unique(User::class, 'phone')->withoutTrashed()],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::findOrCreate('Vendor', 'web');

        $user = User::create([
            'name' => $request->name,
            'role_id' => $role->id,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'pending',
        ]);

        Vendor::create([
            'user_id' => $user->id,
            'store_name' => $request->store_name,
            'business_registration_no' => $request->business_registration_no,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'formatted_address' => $request->formatted_address,
            'status' => 'pending',
        ]);

        $user->assignRole($role->name);

        event(new Registered($user));
        $emails->welcome($user);

        Auth::login($user);

        return redirect()->route('vendor.pending');
    }
}
