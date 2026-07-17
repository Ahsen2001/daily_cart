@php
    $location = null;

    if ($user->customer) {
        $address = $user->customer->addresses->firstWhere('is_default', true) ?? $user->customer->addresses->first();
        if ($address) {
            $location = [
                'label' => __('Default delivery location'),
                'address' => collect([$address->address_line_1, $address->address_line_2, $address->city, $address->district])->filter()->implode(', '),
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
            ];
        }
    } elseif ($user->vendor) {
        $location = [
            'label' => __('Store location'),
            'address' => $user->vendor->formatted_address ?: collect([$user->vendor->address, $user->vendor->city, $user->vendor->district])->filter()->implode(', '),
            'latitude' => $user->vendor->latitude,
            'longitude' => $user->vendor->longitude,
        ];
    } elseif ($user->rider) {
        $location = [
            'label' => __('Rider home base'),
            'address' => $user->rider->formatted_address ?: collect([$user->rider->address, $user->rider->city, $user->rider->district])->filter()->implode(', '),
            'latitude' => $user->rider->latitude,
            'longitude' => $user->rider->longitude,
        ];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div><p class="dc-page-eyebrow">{{ __('Account settings') }}</p><h2 class="dc-page-title">{{ __('Profile') }}</h2></div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container space-y-6">
            @if ($location)
                <div class="dc-panel">
                    <div class="mb-4">
                        <p class="dc-page-eyebrow">{{ __('Location') }}</p>
                        <h3 class="mt-1 text-lg font-bold">{{ __('Your registered map location') }}</h3>
                    </div>
                    <x-location-display
                        :label="$location['label']"
                        :address="$location['address']"
                        :latitude="$location['latitude']"
                        :longitude="$location['longitude']"
                    />
                </div>
            @endif

            <div class="dc-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="dc-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="dc-panel">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
