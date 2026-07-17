@php($location = $profileLocation)

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
