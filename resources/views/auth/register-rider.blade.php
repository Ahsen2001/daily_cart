<x-guest-layout>
    <form method="POST" action="{{ route('rider.register.store') }}">
        @csrf

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

        <div class="flex items-center justify-end mt-4">
            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">{{ __('Already registered?') }}</a>

            <x-primary-button class="ms-4">{{ __('Register as Rider') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
