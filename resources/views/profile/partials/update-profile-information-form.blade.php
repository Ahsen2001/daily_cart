<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" :required="(bool) $profileLocation" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        @if ($profileLocation)
            @php
                $currentDistrict = $user->vendor ? null : old('district', $profileLocation['district']);
                $districtIsConfigured = ! $user->vendor
                    && $deliveryFeeRules->contains(fn ($rule) => strcasecmp($rule->district, (string) $currentDistrict) === 0);
            @endphp

            <section class="rounded-3xl border border-brand-border bg-brand-light/60 p-5">
                <div>
                    <p class="dc-page-eyebrow">{{ $user->vendor ? __('Store location') : __('Delivery & location') }}</p>
                    <h3 class="mt-1 text-lg font-bold text-brand-text">{{ __('Edit your registered address') }}</h3>
                    <p class="mt-1 text-sm leading-6 text-brand-muted">
                        {{ $user->vendor
                            ? __('Keep your store address and map pin accurate for customers and deliveries.')
                            : __('Your saved district determines which active Delivery Fees Configuration is used during checkout.') }}
                    </p>
                </div>

                <div class="mt-5 space-y-4">
                    @if ($user->customer)
                        <div>
                            <x-input-label for="address_line_1" :value="__('Address Line 1')" />
                            <x-text-input id="address_line_1" name="address_line_1" type="text" class="mt-1 block w-full" :value="old('address_line_1', $profileLocation['address_line_1'])" required autocomplete="address-line1" />
                            <x-input-error class="mt-2" :messages="$errors->get('address_line_1')" />
                        </div>

                        <div>
                            <x-input-label for="address_line_2" :value="__('Address Line 2')" />
                            <x-text-input id="address_line_2" name="address_line_2" type="text" class="mt-1 block w-full" :value="old('address_line_2', $profileLocation['address_line_2'])" autocomplete="address-line2" />
                            <x-input-error class="mt-2" :messages="$errors->get('address_line_2')" />
                        </div>
                    @else
                        <div>
                            <x-input-label for="address" :value="$user->vendor ? __('Store Address') : __('Home Base Address')" />
                            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-2xl border-brand-border shadow-sm focus:border-brand-primary focus:ring-brand-primary" required>{{ old('address', $profileLocation['address']) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $profileLocation['city'])" required autocomplete="address-level2" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        @unless ($user->vendor)
                            <div>
                                <x-input-label for="district" :value="__('Delivery District')" />
                                @if ($deliveryFeeRules->isNotEmpty())
                                    <select id="district" name="district" class="mt-1 block w-full rounded-2xl border-brand-border shadow-sm focus:border-brand-primary focus:ring-brand-primary" required autocomplete="address-level1">
                                        <option value="">{{ __('Select supported district') }}</option>
                                        @if ($currentDistrict && ! $districtIsConfigured)
                                            <option value="{{ $currentDistrict }}" selected>{{ $currentDistrict }} — {{ __('not currently configured') }}</option>
                                        @endif
                                        @foreach ($deliveryFeeRules as $rule)
                                            <option value="{{ $rule->district }}" @selected($currentDistrict === $rule->district)>
                                                {{ $rule->district }} — {{ \App\Services\CurrencyService::formatLkr($rule->base_fee) }} + {{ \App\Services\CurrencyService::formatLkr($rule->per_km_fee) }}/km
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <x-text-input id="district" name="district" type="text" class="mt-1 block w-full" :value="$currentDistrict" required autocomplete="address-level1" />
                                @endif
                                <x-input-error class="mt-2" :messages="$errors->get('district')" />
                            </div>
                        @endunless
                    </div>

                    @if ($user->customer)
                        <div>
                            <x-input-label for="postal_code" :value="__('Postal Code')" />
                            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $profileLocation['postal_code'])" autocomplete="postal-code" />
                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                        </div>
                    @endif
                </div>

                @if (! $user->vendor && $deliveryFeeRules->isNotEmpty())
                    <details class="mt-5 rounded-2xl border border-emerald-100 bg-white p-4">
                        <summary class="cursor-pointer text-sm font-bold text-brand-dark">{{ __('View delivery fee criteria') }}</summary>
                        <div class="mt-3 grid gap-3 text-xs sm:grid-cols-2">
                            @foreach ($deliveryFeeRules as $rule)
                                <div class="rounded-2xl bg-brand-light p-3 leading-6 text-brand-text/75">
                                    <p class="font-extrabold text-brand-text">{{ $rule->district }}</p>
                                    <p>{{ __('Base') }}: {{ \App\Services\CurrencyService::formatLkr($rule->base_fee) }} · {{ __('Per km') }}: {{ \App\Services\CurrencyService::formatLkr($rule->per_km_fee) }}</p>
                                    <p>{{ __('Minimum') }}: {{ \App\Services\CurrencyService::formatLkr($rule->minimum_order) }} · {{ __('Free from') }}: {{ $rule->free_delivery_limit !== null ? \App\Services\CurrencyService::formatLkr($rule->free_delivery_limit) : __('Not available') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endif

                <x-map-location-picker
                    :address-input="$user->customer ? 'address_line_1' : 'address'"
                    :latitude="old('latitude', $profileLocation['latitude'])"
                    :longitude="old('longitude', $profileLocation['longitude'])"
                    :formatted-address="old('formatted_address', $profileLocation['formatted_address'])"
                />
            </section>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
