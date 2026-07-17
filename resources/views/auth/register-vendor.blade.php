<x-guest-layout>
    <form method="POST" action="{{ route('vendor.register.store') }}">
        @csrf

        <x-registration-role-tabs active="vendor" />

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-text">{{ __('Register your store') }}</h1>
            <p class="mt-1 text-sm text-brand-muted">{{ __('Tell us about your business. An administrator will review the account before selling is enabled.') }}</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Owner Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="store_name" :value="__('Store Name')" />
            <x-text-input id="store_name" class="block mt-1 w-full" type="text" name="store_name" :value="old('store_name')" required />
            <x-input-error :messages="$errors->get('store_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="business_registration_no" :value="__('Business Registration Number')" />
            <x-text-input id="business_registration_no" class="block mt-1 w-full" type="text" name="business_registration_no" :value="old('business_registration_no')" />
            <x-input-error :messages="$errors->get('business_registration_no')" class="mt-2" />
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

        <div class="mt-4">
            <x-input-label for="address" :value="__('Store Address')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" required autocomplete="street-address" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="district" :value="__('District')" />
                <x-text-input id="district" class="block mt-1 w-full" type="text" name="district" :value="old('district')" required />
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

            <x-primary-button class="w-full sm:w-auto">{{ __('Register as Vendor') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
