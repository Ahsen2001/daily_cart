<x-guest-layout>
    <x-slot name="title">{{ __('Sign in') }}</x-slot>

    <div class="flex items-start justify-between gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-primary text-white shadow-sm">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V6l-7-3Zm-3 9 2 2 4-4" /></svg>
        </div>
        <a href="{{ url('/') }}" class="inline-flex min-h-10 items-center gap-2 rounded-full px-3 text-sm font-bold text-brand-muted transition hover:bg-brand-light hover:text-brand-dark">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            {{ __('Home') }}
        </a>
    </div>

    <div class="mt-6">
        <p class="dc-page-eyebrow">{{ __('Secure account access') }}</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-brand-text">{{ __('Welcome back') }}</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">{{ __('Sign in once to open the correct DailyCart workspace for your account.') }}</p>
    </div>

    <x-auth-session-status class="dc-flash dc-flash-success mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" x-data="{ showPassword: false }">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-brand-muted" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4V6Zm0 1 8 6 8-6" /></svg>
                </span>
                <x-text-input id="email" class="block w-full py-3 pl-12 pr-4" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" inputmode="email" placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-brand-dark hover:underline" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                @endif
            </div>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-brand-muted" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                </span>
                <input id="password" class="block min-h-11 w-full rounded-2xl border-brand-border bg-white py-3 pl-12 pr-12 text-brand-text shadow-sm transition placeholder:text-brand-muted/70 focus:border-brand-primary focus:ring-brand-primary" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="{{ __('Enter your password') }}">
                <button type="button" @click="showPassword = ! showPassword" :aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'" :aria-pressed="showPassword.toString()" class="absolute inset-y-0 right-0 flex min-h-11 min-w-11 items-center justify-center rounded-r-2xl text-brand-muted transition hover:text-brand-dark">
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    <svg x-cloak x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="m4 4 16 16M10.7 6.1A10.3 10.3 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.1 2.7M6.6 6.6C3.9 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.1 0 2.1-.2 3-.5M10 10a2.8 2.8 0 0 0 4 4" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex cursor-pointer items-start gap-3 rounded-2xl border border-transparent p-2 transition hover:border-brand-border hover:bg-brand-light/60">
            <input id="remember_me" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-brand-border text-brand-primary shadow-sm focus:ring-brand-primary" name="remember">
            <span><span class="block text-sm font-bold text-brand-text">{{ __('Keep me signed in') }}</span><span class="block text-xs text-brand-muted">{{ __('Use only on a trusted personal device.') }}</span></span>
        </label>

        <x-primary-button class="w-full py-3">
            <span>{{ __('Sign in securely') }}</span>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
        </x-primary-button>
    </form>

    <div class="my-6 flex items-center gap-3" aria-hidden="true"><span class="h-px flex-1 bg-brand-border"></span><span class="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-muted">{{ __('New to DailyCart?') }}</span><span class="h-px flex-1 bg-brand-border"></span></div>

    <a href="{{ route('register') }}" class="dc-button-secondary w-full">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19a6 6 0 0 0-12 0m6-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm9-3v6m3-3h-6" /></svg>
        {{ __('Create customer account') }}
    </a>

    <div class="mt-3 grid grid-cols-2 gap-2">
        <a href="{{ route('vendor.register') }}" class="rounded-2xl border border-brand-border bg-brand-light/50 p-3 text-center text-xs font-bold text-brand-dark transition hover:border-brand-primary hover:bg-brand-light">{{ __('Register as Vendor') }}</a>
        <a href="{{ route('rider.register') }}" class="rounded-2xl border border-brand-border bg-brand-light/50 p-3 text-center text-xs font-bold text-brand-dark transition hover:border-brand-primary hover:bg-brand-light">{{ __('Register as Rider') }}</a>
    </div>

    <div class="mt-6 flex items-center justify-center gap-2 text-center text-xs font-medium text-brand-muted">
        <svg class="h-4 w-4 text-brand-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V6l-7-3Z" /></svg>
        {{ __('One secure login for customers, vendors, riders, and administrators.') }}
    </div>
</x-guest-layout>
