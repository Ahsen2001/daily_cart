<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Keep the title expression ASCII-only for consistent encoding across Windows editors. --}}
        {{--
        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name', 'DailyCart') }}</title>
        --}}
        <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'DailyCart') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-brand-text antialiased">
        @php
            $isRegistration = request()->routeIs('register', 'vendor.register', 'rider.register');
            $isLogin = request()->routeIs('login');
        @endphp
        <a href="#main-content" class="dc-skip-link">{{ __('Skip to main content') }}</a>
        <div class="min-h-screen bg-brand-light">
            <main
                id="main-content"
                @class([
                    'mx-auto grid min-h-screen max-w-7xl items-center gap-10 px-4 py-8 xl:px-8',
                    'xl:grid-cols-[minmax(0,.8fr)_minmax(620px,1.2fr)]' => $isRegistration,
                    'xl:grid-cols-[minmax(0,1fr)_460px]' => ! $isRegistration,
                ])
                tabindex="-1"
            >
                <section class="hidden xl:block">
                    <a href="/" class="inline-flex"><x-application-logo /></a>
                    <p class="dc-page-eyebrow mt-12">{{ $isLogin ? __('Your secure workspace') : __('Shop with confidence') }}</p>
                    <h1 class="mt-3 max-w-xl text-5xl font-extrabold leading-tight tracking-tight">
                        {{ $isLogin ? __('One account. Every DailyCart experience.') : __('Fresh essentials, reliable delivery, one secure account.') }}
                    </h1>
                    <p class="mt-5 max-w-lg text-lg leading-8 text-brand-muted">
                        {{ $isLogin ? __('Customers shop, vendors manage stores, riders coordinate deliveries, and teams operate the platform from one role-aware sign-in.') : __('Manage orders, payments, delivery schedules, rewards, and support from one simple DailyCart experience.') }}
                    </p>
                    @if ($isLogin)
                        <div class="mt-8 max-w-lg space-y-3">
                            @foreach ([
                                [__('Protected access'), __('Encrypted sessions and optional OTP verification.')],
                                [__('Role-aware routing'), __('Open the correct workspace automatically after sign-in.')],
                                [__('Reliable recovery'), __('Reset access securely whenever you need it.')],
                            ] as [$heading, $description])
                                <div class="flex items-start gap-3 rounded-2xl border border-brand-border bg-white/80 p-4 shadow-sm">
                                    <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-primary text-white">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 12 4 4 8-8" /></svg>
                                    </span>
                                    <div><strong class="block text-sm text-brand-text">{{ $heading }}</strong><span class="mt-0.5 block text-xs leading-5 text-brand-muted">{{ $description }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">LKR</strong><span class="text-xs text-brand-muted">{{ __('Local pricing') }}</span></div>
                            <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">30m</strong><span class="text-xs text-brand-muted">{{ __('Scheduling') }}</span></div>
                            <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">4</strong><span class="text-xs text-brand-muted">{{ __('Payment options') }}</span></div>
                        </div>
                    @endif
                </section>

                <section @class(['mx-auto w-full', 'max-w-3xl' => $isRegistration, 'max-w-[460px]' => ! $isRegistration])>
                    <div class="mb-7 flex justify-center lg:hidden">
                        <a href="/"><x-application-logo /></a>
                    </div>
                    <div class="overflow-hidden rounded-[2rem] border border-brand-border bg-white p-5 shadow-soft sm:p-8">
                        {{ $slot }}
                    </div>
                    <p class="mt-5 text-center text-xs text-brand-muted">{{ __('Protected by encrypted sessions and secure password hashing.') }}</p>
                </section>
            </main>
        </div>
    </body>
</html>
