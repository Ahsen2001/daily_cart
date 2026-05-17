<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter the 6-digit OTP sent to your email address to finish logging in.') }}
    </div>

    <form method="POST" action="{{ route('login.otp.verify') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="code" :value="__('OTP Code')" />
            <x-text-input id="code" class="mt-1 block w-full" type="text" name="code" maxlength="6" inputmode="numeric" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button>{{ __('Verify and Log in') }}</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('login.otp.resend') }}" class="mt-4">
        @csrf
        <button class="text-sm font-medium text-green-700 underline">{{ __('Resend OTP') }}</button>
    </form>
</x-guest-layout>
