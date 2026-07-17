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
        <a href="#main-content" class="dc-skip-link">{{ __('Skip to main content') }}</a>
        <div class="min-h-screen bg-brand-light">
            <main id="main-content" class="mx-auto grid min-h-screen max-w-6xl items-center gap-10 px-4 py-8 lg:grid-cols-[minmax(0,1fr)_460px] lg:px-8" tabindex="-1">
                <section class="hidden lg:block">
                    <a href="/" class="inline-flex"><x-application-logo /></a>
                    <p class="dc-page-eyebrow mt-12">{{ __('Shop with confidence') }}</p>
                    <h1 class="mt-3 max-w-xl text-5xl font-extrabold leading-tight tracking-tight">{{ __('Fresh essentials, reliable delivery, one secure account.') }}</h1>
                    <p class="mt-5 max-w-lg text-lg leading-8 text-brand-muted">{{ __('Manage orders, payments, delivery schedules, rewards, and support from one simple DailyCart experience.') }}</p>
                    <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">LKR</strong><span class="text-xs text-brand-muted">{{ __('Local pricing') }}</span></div>
                        <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">30m</strong><span class="text-xs text-brand-muted">{{ __('Scheduling') }}</span></div>
                        <div class="rounded-2xl border border-brand-border bg-white/80 p-4"><strong class="block text-brand-dark">4</strong><span class="text-xs text-brand-muted">{{ __('Payment options') }}</span></div>
                    </div>
                </section>

                <section class="w-full">
                    <div class="mb-7 flex justify-center lg:hidden">
                        <a href="/"><x-application-logo /></a>
                    </div>
                    <div class="overflow-hidden rounded-[2rem] border border-brand-border bg-white p-6 shadow-soft sm:p-8">
                        {{ $slot }}
                    </div>
                    <p class="mt-5 text-center text-xs text-brand-muted">{{ __('Protected by encrypted sessions and secure password hashing.') }}</p>
                </section>
            </main>
        </div>
    </body>
</html>
