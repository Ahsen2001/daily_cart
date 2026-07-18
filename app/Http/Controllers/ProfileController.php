<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\DeliveryFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, DeliveryFeeService $deliveryFees): View
    {
        $user = $request->user()->load(['customer.addresses', 'vendor', 'rider']);

        return view('profile.edit', [
            'user' => $user,
            'profileLocation' => $this->profileLocation($user),
            'deliveryFeeRules' => $deliveryFees->configuredRules(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user()->load(['customer.addresses', 'vendor', 'rider']);

        DB::transaction(function () use ($user, $validated) {
            $user->fill(Arr::only($validated, ['name', 'email', 'phone']));

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            $this->updateLocation($user, $validated);
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, AccountDeletionService $accounts): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $accounts->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /** @return array<string, mixed>|null */
    private function profileLocation(User $user): ?array
    {
        if ($user->customer) {
            $address = $user->customer->addresses->firstWhere('is_default', true)
                ?? $user->customer->addresses->first();

            return [
                'type' => 'customer',
                'label' => __('Default delivery location'),
                'address' => collect([$address?->address_line_1, $address?->address_line_2, $address?->city, $address?->district])->filter()->implode(', '),
                'address_line_1' => $address?->address_line_1,
                'address_line_2' => $address?->address_line_2,
                'city' => $address?->city,
                'district' => $address?->district,
                'postal_code' => $address?->postal_code,
                'latitude' => $address?->latitude,
                'longitude' => $address?->longitude,
                'formatted_address' => collect([$address?->address_line_1, $address?->city, $address?->district])->filter()->implode(', '),
            ];
        }

        $roleProfile = $user->vendor ?? $user->rider;

        if (! $roleProfile) {
            return null;
        }

        return [
            'type' => $user->vendor ? 'vendor' : 'rider',
            'label' => $user->vendor ? __('Store location') : __('Rider home base'),
            'address' => $roleProfile->address,
            'city' => $roleProfile->city,
            'district' => $user->vendor ? null : $roleProfile->district,
            'latitude' => $roleProfile->latitude,
            'longitude' => $roleProfile->longitude,
            'formatted_address' => $roleProfile->formatted_address
                ?: collect([
                    $roleProfile->address,
                    $roleProfile->city,
                    $user->vendor ? null : $roleProfile->district,
                ])->filter()->implode(', '),
        ];
    }

    /** @param array<string, mixed> $validated */
    private function updateLocation(User $user, array $validated): void
    {
        if ($user->customer) {
            $customer = $user->customer;
            $address = $customer->addresses->firstWhere('is_default', true)
                ?? $customer->addresses->first()
                ?? $customer->addresses()->make();

            $customer->addresses()
                ->when($address->exists, fn ($query) => $query->whereKeyNot($address->id))
                ->update(['is_default' => false]);

            $address->fill([
                'label' => $address->label ?: 'Home',
                'recipient_name' => $user->name,
                'phone' => $validated['phone'],
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'] ?? null,
                'city' => $validated['city'],
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'is_default' => true,
            ])->save();

            $customer->update(['phone' => $validated['phone']]);

            return;
        }

        $roleProfile = $user->vendor ?? $user->rider;

        if (! $roleProfile) {
            return;
        }

        $location = [
            'address' => $validated['address'],
            'city' => $validated['city'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'formatted_address' => $validated['formatted_address']
                ?? collect([
                    $validated['address'],
                    $validated['city'],
                    $validated['district'] ?? null,
                ])->filter()->implode(', '),
        ];

        if ($user->vendor) {
            $location['phone'] = $validated['phone'];
        } else {
            $location['district'] = $validated['district'];
        }

        $roleProfile->update($location);
    }
}
