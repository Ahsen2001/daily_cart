<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>{{ __('Stores') }} - DailyCart</title>

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
    <header class="sticky top-0 z-40 border-b border-brand-border bg-white/95 shadow-sm backdrop-blur-xl">
        <div class="dc-container flex min-h-16 items-center justify-between gap-3 py-2 sm:min-h-20">

            {{-- Logo --}}
            <a
                href="{{ route('home') }}"
                class="min-w-0 transition duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                <x-application-logo />
            </a>

            <div class="flex shrink-0 items-center gap-2">

                {{-- Mobile Back button --}}
                <a
                    class="dc-button-secondary inline-flex min-h-10 items-center gap-1.5 px-3 text-xs lg:hidden"
                    href="{{ route('home') }}"
                    aria-label="{{ __('Back to Home') }}">

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

                {{-- Desktop navigation --}}
                <nav
                    class="hidden items-center gap-2 lg:flex"
                    aria-label="{{ __('Store navigation') }}">

                    <a
                        class="dc-button-secondary"
                        href="{{ route('home') }}">

                        {{ __('← Back to Home') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.about') }}">

                        {{ __('About') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.offers') }}">

                        {{ __('Offers') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('stores.index') }}"
                        aria-current="page">

                        {{ __('Stores') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.contact') }}">

                        {{ __('Contact') }}
                    </a>

                    <a
                        class="dc-button"
                        href="{{ route('products.index') }}">

                        {{ __('Products') }}
                    </a>
                </nav>
            </div>
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

                {{-- Heading and search --}}
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand-dark">
                            {{ __('DailyCart Marketplace') }}
                        </p>

                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-brand-text sm:text-3xl">
                            {{ __('Browse Stores') }}
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-text/65">
                            {{ __('Discover approved vendor storefronts while continuing to shop products across every vendor.') }}
                        </p>
                    </div>

                    {{-- Store filters --}}
                    <form
                        method="GET"
                        action="{{ route('stores.index') }}"
                        class="grid w-full gap-3 sm:grid-cols-[minmax(0,1fr)_200px_auto] lg:max-w-2xl">

                        <x-search-bar
                            name="search"
                            placeholder="Search stores..."
                            :value="request('search')" />

                        <select
                            name="category"
                            class="rounded-xl border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">

                            <option value="">
                                {{ __('All categories') }}
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(
                                        (string) request('category')
                                        === (string) $category->id
                                    )>

                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <button
                            class="dc-button justify-center"
                            type="submit">

                            {{ __('Search') }}
                        </button>
                    </form>
                </div>

                {{-- Store-list information --}}
                <div class="mt-8 flex flex-col gap-3 border-b border-brand-border pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-semibold text-brand-text/60">
                        {{ __('Approved storefronts') }}
                    </p>

                    <a
                        class="inline-flex w-fit items-center gap-2 text-sm font-bold text-brand-dark transition hover:text-brand-primary"
                        href="{{ route('stores.index', [
                            'featured' => 1
                        ]) }}">

                        {{ __('Featured stores') }}

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
                                d="M5 12h14m-5-5 5 5-5 5">
                            </path>
                        </svg>
                    </a>
                </div>

                {{-- Stores grid --}}
                <div class="mt-6 grid auto-rows-fr grid-cols-1 items-stretch gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                    @forelse ($stores as $store)
                        <x-store-card
                            :store="$store"
                            class="h-full" />
                    @empty
                        <div class="dc-card sm:col-span-2 md:col-span-3 lg:col-span-5">
                            <p class="text-sm text-brand-text/65">
                                {{ __('No stores match those filters yet.') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $stores->links() }}
                </div>
            </div>
        </section>
    </main>

    <x-footer />
</body>

</html>