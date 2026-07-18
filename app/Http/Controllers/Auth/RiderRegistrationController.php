<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\Role;
use App\Models\User;
use App\Services\ExternalEmailService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RiderRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-rider');
    }

    public function store(Request $request, ExternalEmailService $emails): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->withoutTrashed()],
            'phone' => ['required', 'string', 'max:30', Rule::unique(User::class, 'phone')->withoutTrashed()],
            'vehicle_type' => ['required', Rule::in(['bicycle', 'motorbike', 'three_wheeler', 'van'])],
            'vehicle_number' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255', Rule::unique(Rider::class, 'license_number')->withoutTrashed()],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::findOrCreate('Rider', 'web');

        $user = User::create([
            'name' => $request->name,
            'role_id' => $role->id,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'pending',
        ]);

        Rider::create([
            'user_id' => $user->id,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'license_number' => $request->license_number,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'formatted_address' => $request->formatted_address,
            'availability_status' => 'unavailable',
            'verification_status' => 'pending',
        ]);

        $user->assignRole($role->name);

        event(new Registered($user));
        $emails->welcome($user);

        Auth::login($user);

        return redirect()->route('rider.pending');
    }
}
