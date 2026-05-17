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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'vehicle_type' => ['required', Rule::in(['bicycle', 'motorbike', 'three_wheeler', 'van'])],
            'vehicle_number' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255', 'unique:riders,license_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::where('name', 'Rider')->firstOrFail();

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
