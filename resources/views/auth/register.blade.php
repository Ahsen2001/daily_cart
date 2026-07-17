<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <x-registration-role-tabs active="customer" />

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-brand-text">{{ __('Create your customer account') }}</h1>
            <p class="mt-1 text-sm text-brand-muted">{{ __('Save your delivery details and start shopping with DailyCart.') }}</p>
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address_line_1" :value="__('Address Line 1')" />
            <x-text-input id="address_line_1" class="block mt-1 w-full" type="text" name="address_line_1" :value="old('address_line_1')" required />
            <x-input-error :messages="$errors->get('address_line_1')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="address_line_2" :value="__('Address Line 2')" />
            <x-text-input id="address_line_2" class="block mt-1 w-full" type="text" name="address_line_2" :value="old('address_line_2')" />
            <x-input-error :messages="$errors->get('address_line_2')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-3">
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

            <div>
                <x-input-label for="postal_code" :value="__('Postal Code')" />
                <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code')" />
                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
            </div>
        </div>

        <x-map-location-picker address-input="address_line_1" />

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-7 flex flex-col-reverse gap-3 border-t border-brand-border pt-6 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-center text-sm font-semibold text-brand-muted hover:text-brand-dark hover:underline" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="w-full sm:w-auto">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
