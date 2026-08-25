<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>{{ __('Categories') }} - DailyCart</title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/logo.png') }}">

    <link
        rel="shortcut icon"
        type="image/png"
        href="{{ asset('images/logo.png') }}">

    <link
        rel="preconnect"
        href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap"
        rel="stylesheet">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])
</head>

<body class="bg-brand-light font-sans text-brand-text antialiased">

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 shadow-sm backdrop-blur-xl">
        <div class="dc-container flex min-h-16 items-center justify-between gap-3 py-2 sm:min-h-20">

            {{-- Logo --}}
            <a
                href="{{ url('/') }}"
                class="min-w-0 transition duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                <x-application-logo />
            </a>

            {{-- Navigation --}}
            <nav
                class="flex shrink-0 items-center gap-2 sm:gap-3"
                aria-label="{{ __('Category navigation') }}">

                {{-- Mobile home button --}}
                <a
                    class="dc-button-secondary inline-flex min-h-10 items-center gap-1.5 px-3 text-xs md:hidden"
                    href="{{ route('home') }}"
                    aria-label="{{ __('Back Home') }}">

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                        aria-hidden="true">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 12H5m6-6-6 6 6 6">
                        </path>
                    </svg>
                </a>

                {{-- Desktop authentication --}}
                <div class="hidden items-center gap-3 md:flex">
                    @auth
                    <a
                        class="dc-button-secondary"
                        href="{{ route('dashboard') }}">

                        {{ __('Dashboard') }}
                    </a>
                    @else
                    <a
                        class="dc-button-secondary"
                        href="{{ route('login') }}">

                        {{ __('Login') }}
                    </a>

                    <a
                        class="dc-button"
                        href="{{ route('register') }}">

                        {{ __('Start Shopping') }}
                    </a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden bg-white py-8 sm:py-10 lg:py-12">

            {{-- Decorative background --}}
            <div
                class="pointer-events-none absolute -left-32 -top-32 h-72 w-72 rounded-full bg-brand-primary/5 blur-3xl"
                aria-hidden="true">
            </div>

            <div
                class="pointer-events-none absolute -bottom-40 right-0 h-80 w-80 rounded-full bg-brand-orange/5 blur-3xl"
                aria-hidden="true">
            </div>

            <div class="dc-container relative">

                {{-- Section heading --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand-dark">
                            {{ __('DailyCart Marketplace') }}
                        </p>

                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-brand-text sm:text-3xl">
                            {{ __('Browse Categories') }}
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-text/65">
                            {{ __('Explore approved DailyCart products by category and find your daily essentials quickly.') }}
                        </p>
                    </div>

                    <a
                        class="dc-button-secondary inline-flex min-h-10 w-fit items-center gap-2 self-start px-4 py-2.5 text-xs sm:self-auto sm:text-sm"
                        href="{{ url('/') }}">

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 12H5m6-6-6 6 6 6">
                            </path>
                        </svg>

                        {{ __('Back Home') }}
                    </a>
                </div>

                {{-- Categories grid --}}
                <div class="mt-8 grid auto-rows-fr grid-cols-1 items-stretch gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                    @forelse ($categories as $category)
                    <a
                        href="{{ route('products.index', [
                                'category' => $category->slug
                            ]) }}"
                        class="group flex h-full flex-col overflow-hidden rounded-2xl border border-green-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-soft focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                        {{-- Category image --}}
                        <div class="aspect-[16/10] shrink-0 overflow-hidden bg-brand-light">
                            <img
                                src="{{ $category->display_image_url }}"
                                alt="{{ $category->name }}"
                                class="block h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                                decoding="async">
                        </div>

                        {{-- Category information --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="line-clamp-2 text-sm font-bold leading-5 text-brand-text transition group-hover:text-brand-primary sm:text-base">
                                    {{ $category->name }}
                                </h2>

                                {{-- Product count --}}
                                <span
                                    class="inline-flex min-w-7 shrink-0 items-center justify-center rounded-full bg-brand-light px-2.5 py-1 text-[10px] font-bold text-brand-dark"
                                    aria-label="{{ trans_choice(
                                            ':count available product|:count available products',
                                            $category->available_products_count,
                                            ['count' => $category->available_products_count]
                                        ) }}">

                                    {{ $category->available_products_count }}
                                </span>
                            </div>

                            <p class="mt-2 line-clamp-3 text-xs leading-5 text-brand-text/60">
                                {{
                                        $category->description
                                        ?: __('Fresh DailyCart essentials selected for this category.')
                                    }}
                            </p>

                            {{-- Browse indicator --}}
                            <div class="mt-auto flex items-center justify-between border-t border-brand-border pt-3">
                                <span class="text-xs font-bold text-brand-dark">
                                    {{ __('Browse products') }}
                                </span>

                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-light text-brand-dark transition duration-300 group-hover:translate-x-0.5 group-hover:bg-brand-primary group-hover:text-white"
                                    aria-hidden="true">

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24">

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 12h14m-5-5 5 5-5 5">
                                        </path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="dc-card sm:col-span-2 md:col-span-3 lg:col-span-5">
                        <p class="text-sm text-brand-text/70">
                            {{ __('No active categories are available yet.') }}
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    <x-footer />
</body>

</html>