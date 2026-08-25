<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        {{ $selectedCategory?->name ?? __('Products') }} - DailyCart
    </title>

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

            {{-- DailyCart logo --}}
            <a
                href="{{ route('home') }}"
                class="min-w-0 transition duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                <x-application-logo />
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                {{-- Back to Home --}}
                <a
                    class="dc-button-secondary inline-flex min-h-10 items-center gap-1.5 px-3 text-xs sm:px-5 sm:text-sm"
                    href="{{ route('home') }}"
                    aria-label="{{ __('Back to Home') }}">

                    <svg
                        class="h-4 w-4 shrink-0"
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

                    <span class="hidden sm:inline">
                        {{ __('Back to Home') }}
                    </span>
                </a>

                {{-- Desktop navigation --}}
                <nav
                    class="hidden items-center gap-3 md:flex"
                    aria-label="{{ __('Product navigation') }}">

                    <a
                        class="dc-button-secondary"
                        href="{{ route('categories.index') }}">

                        {{ __('Categories') }}
                    </a>

                    @auth
                    <a
                        class="dc-button"
                        href="{{ route('dashboard') }}">

                        {{ __('Dashboard') }}
                    </a>
                    @else
                    <a
                        class="dc-button"
                        href="{{ route('login') }}">

                        {{ __('Login') }}
                    </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="bg-white py-8 sm:py-10 lg:py-12">
            <div class="dc-container">

                {{-- Page heading and search --}}
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-brand-dark">
                            {{ __('Available Products') }}
                        </p>

                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">
                            {{ $selectedCategory?->name ?? __('All Approved Products') }}
                        </h1>
                    </div>

                    {{-- Search and category filter --}}
                    <form
                        method="GET"
                        action="{{ route('products.index') }}"
                        class="grid w-full gap-3 sm:grid-cols-[minmax(0,1fr)_200px_auto] lg:max-w-2xl">

                        <x-search-bar
                            name="search"
                            placeholder="Search products or brands..."
                            :value="request('search')" />

                        <select
                            name="category"
                            class="rounded-xl border-brand-border text-sm shadow-sm focus:border-brand-primary focus:ring-brand-primary">

                            <option value="">
                                {{ __('All categories') }}
                            </option>

                            @foreach ($categories as $category)
                            <option
                                value="{{ $category->slug }}"
                                @selected(
                                request('category')===$category->slug
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

                {{-- Products grid --}}
                <div class="mt-8 grid auto-rows-fr grid-cols-1 items-stretch gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                    @forelse ($products as $product)
                    @php
                    $price = $product->discount_price
                    ?: $product->price;
                    @endphp

                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-green-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-soft">

                        {{-- Product image --}}
                        <a
                            href="{{ route('products.show', [
                                    'product' => $product->slug
                                ]) }}"
                            class="block shrink-0 overflow-hidden">

                            <div class="aspect-[16/10] overflow-hidden bg-brand-light">
                                <img
                                    src="{{ $product->display_image_url }}"
                                    alt="{{ $product->name }}"
                                    class="block h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        </a>

                        {{-- Product information --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div>
                                {{-- Category --}}
                                <p class="text-[11px] font-medium text-brand-dark">
                                    {{ $product->category?->name }}
                                </p>

                                {{-- Product name --}}
                                <a
                                    href="{{ route('products.show', [
                                            'product' => $product->slug
                                        ]) }}"
                                    class="block">

                                    <h2 class="mt-1 line-clamp-2 text-sm font-bold leading-5 text-brand-text transition hover:text-brand-primary">
                                        {{ $product->name }}
                                    </h2>
                                </a>

                                {{-- Vendor --}}
                                @if ($product->vendor?->storeProfile)
                                <p class="mt-2 line-clamp-2 text-xs leading-5 text-brand-text/60">
                                    {{ __('Sold by:') }}

                                    <a
                                        href="{{ route(
                                                    'stores.show',
                                                    $product->vendor->storeProfile
                                                ) }}"
                                        class="font-bold text-brand-dark transition hover:text-brand-primary hover:underline">

                                        {{ $product->vendor->store_name }}
                                    </a>
                                </p>
                                @endif

                                {{-- Brand --}}
                                @if ($product->brand)
                                <p class="mt-1 line-clamp-1 text-xs text-brand-text/55">
                                    {{ $product->brand }}
                                </p>
                                @endif
                            </div>

                            {{-- Price and View button --}}
                            <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                                <p class="min-w-0 text-sm font-bold text-brand-dark">
                                    {{ \App\Services\CurrencyService::formatLkr(
                                            $price
                                        ) }}
                                </p>

                                <a
                                    class="dc-button-secondary shrink-0 px-3.5 py-2 text-xs"
                                    href="{{ route('products.show', [
                                            'product' => $product->slug
                                        ]) }}">

                                    {{ __('View') }}
                                </a>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="dc-card sm:col-span-2 md:col-span-3 lg:col-span-5">
                        <p class="text-sm text-brand-text/70">
                            {{ __('No approved products are available for this category yet.') }}
                        </p>
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            </div>
        </section>
    </main>

    <x-footer />
</body>

</html>