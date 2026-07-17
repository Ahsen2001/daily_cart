<x-guest-layout>
    <form method="POST" action="{{ route('rider.register.store') }}">
        @csrf

        <x-registration-role-tabs active="rider" />

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-text">{{ __('Join the delivery team') }}</h1>
            <p class="mt-1 text-sm text-brand-muted">{{ __('Add your vehicle and home base details for account verification and delivery assignment.') }}</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-3">
            <div>
                <x-input-label for="vehicle_type" :value="__('Vehicle Type')" />
                <select id="vehicle_type" name="vehicle_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="motorbike" @selected(old('vehicle_type') === 'motorbike')>{{ __('Motorbike') }}</option>
                    <option value="bicycle" @selected(old('vehicle_type') === 'bicycle')>{{ __('Bicycle') }}</option>
                    <option value="three_wheeler" @selected(old('vehicle_type') === 'three_wheeler')>{{ __('Three Wheeler') }}</option>
                    <option value="van" @selected(old('vehicle_type') === 'van')>{{ __('Van') }}</option>
                </select>
                <x-input-error :messages="$errors->get('vehicle_type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="vehicle_number" :value="__('Vehicle Number')" />
                <x-text-input id="vehicle_number" class="block mt-1 w-full" type="text" name="vehicle_number" :value="old('vehicle_number')" />
                <x-input-error :messages="$errors->get('vehicle_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="license_number" :value="__('License Number')" />
                <x-text-input id="license_number" class="block mt-1 w-full" type="text" name="license_number" :value="old('license_number')" />
                <x-input-error :messages="$errors->get('license_number')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Home Base Address')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" required autocomplete="street-address" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" class="mt-1 block w-full" type="text" name="city" :value="old('city')" required autocomplete="address-level2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="district" :value="__('District')" />
                <x-text-input id="district" class="mt-1 block w-full" type="text" name="district" :value="old('district')" required autocomplete="address-level1" />
                <x-input-error :messages="$errors->get('district')" class="mt-2" />
            </div>
        </div>

        <x-map-location-picker address-input="address" />

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="mt-7 flex flex-col-reverse gap-3 border-t border-brand-border pt-6 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-center text-sm font-semibold text-brand-muted hover:text-brand-dark hover:underline" href="{{ route('login') }}">{{ __('Already registered?') }}</a>

            <x-primary-button class="w-full sm:w-auto">{{ __('Register as Rider') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
