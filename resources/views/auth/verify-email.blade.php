<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    @if (session('status') == 'verification-otp-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new OTP has been sent to your email address.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.otp.verify') }}" class="mb-4 space-y-3">
        @csrf
        <div>
            <x-input-label for="code" :value="__('Email OTP Code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" maxlength="6" inputmode="numeric" required />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <x-primary-button>{{ __('Verify OTP') }}</x-primary-button>
    </form>

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.otp.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Send Verification OTP') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
