@php
    $featuredProducts = \App\Models\Product::query()
        ->visibleToCustomers()
        ->with(['category', 'vendor', 'images'])
        ->latest()
        ->limit(4)
        ->get();

    $homepageCategories = [
        ['name' => 'Grocery', 'slug' => 'grocery', 'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80'],
        ['name' => 'Vegetables', 'slug' => 'vegetables', 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=900&q=80'],
        ['name' => 'Bakery', 'slug' => 'bakery', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=80'],
        ['name' => 'Pharmacy', 'slug' => 'pharmacy', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=900&q=80'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>DailyCart</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-light font-sans text-brand-text">
        <header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-brand-border bg-white/95 backdrop-blur-xl">
            <div class="dc-container flex h-20 items-center justify-between gap-4">
                <a href="/" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <div class="hidden items-center gap-3 md:flex">
                    <nav class="flex items-center rounded-full border border-brand-border bg-brand-light/70 p-1" aria-label="{{ __('Primary navigation') }}">
                        <a class="dc-public-nav-link" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                        <a class="dc-public-nav-link" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                        <a class="dc-public-nav-link" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                    </nav>
                    <a class="dc-button-secondary px-5" href="{{ route('login') }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 12a7 7 0 0 0-14 0"/></svg>
                        {{ __('Login') }}
                    </a>
                    <a class="dc-button px-6" href="{{ route('register') }}">
                        {{ __('Start Shopping') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                    </a>
                </div>
                <button @click="open = ! open" :aria-expanded="open.toString()" aria-controls="public-mobile-navigation" aria-label="{{ __('Toggle navigation') }}" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-2xl bg-brand-light text-brand-dark md:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="!open" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                        <path x-cloak x-show="open" stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
            <nav id="public-mobile-navigation" x-cloak x-show="open" x-transition class="border-t border-brand-border bg-white p-4 shadow-lift md:hidden" aria-label="{{ __('Mobile navigation') }}">
                <div class="grid gap-2">
                    <a class="dc-sidebar-link" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                    <a class="dc-sidebar-link" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    <a class="dc-sidebar-link" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a class="dc-button-secondary" href="{{ route('login') }}">{{ __('Login') }}</a>
                        <a class="dc-button" href="{{ route('register') }}">{{ __('Start Shopping') }}</a>
                    </div>
                </div>
            </nav>
        </header>

        <main>
            <section class="dc-container grid min-h-[72vh] items-center gap-10 py-10 sm:py-14 lg:grid-cols-[1.05fr_.95fr]">
                <div class="animate-fade-up">
                    <x-notification-badge>{{ __('Fresh Daily Essentials') }}</x-notification-badge>
                    <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight text-brand-text sm:text-5xl lg:text-6xl">
                        Smart shopping and daily essentials delivery for your home.
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-brand-text/70">
                        DailyCart brings groceries, vegetables, fruits, household items, bakery goods, pharmacy products, and more into one clean delivery platform.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a class="dc-button" href="{{ route('register') }}">{{ __('Create Customer Account') }}</a>
                        <a class="dc-button-secondary" href="{{ route('vendor.register') }}">{{ __('Become a Vendor') }}</a>
                    </div>
                </div>
                <div class="relative animate-fade-up">
                    <div class="rounded-[2rem] border border-brand-border bg-white p-5 shadow-soft">
                        <img src="{{ asset('images/logo.png') }}" alt="DailyCart logo" class="mx-auto h-56 w-56 rounded-3xl object-contain">
                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-3xl bg-brand-light p-4 text-center"><p class="text-2xl font-bold text-brand-dark">30+</p><p class="text-xs text-brand-text/60">Categories</p></div>
                            <div class="rounded-3xl bg-brand-light p-4 text-center"><p class="text-2xl font-bold text-brand-dark">LKR</p><p class="text-xs text-brand-text/60">Only</p></div>
                            <div class="rounded-3xl bg-brand-light p-4 text-center"><p class="text-2xl font-bold text-brand-dark">30m</p><p class="text-xs text-brand-text/60">Min schedule</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white py-14">
                <div class="dc-container">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="font-semibold text-brand-dark">{{ __('Popular Products') }}</p>
                            <h2 class="mt-2 text-3xl font-extrabold">{{ __('Fresh picks for today') }}</h2>
                        </div>
                        <a class="dc-button-secondary" href="{{ route('categories.index') }}">{{ __('Browse All') }}</a>
                    </div>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($homepageCategories as $category)
                            <a href="{{ route('products.index', ['category' => $category['slug']]) }}" class="dc-card block overflow-hidden p-0 text-center">
                                <div class="aspect-[4/3] overflow-hidden bg-brand-light">
                                    <img
                                        src="{{ $category['image'] }}"
                                        alt="{{ $category['name'] }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </div>
                                <div class="p-6">
                                    <h3 class="font-bold">{{ $category['name'] }}</h3>
                                    <p class="mt-2 text-sm text-brand-text/60">{{ __('View approved products in this category.') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if ($featuredProducts->isNotEmpty())
                        <div class="mt-12 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="font-semibold text-brand-dark">{{ __('Featured Products') }}</p>
                                <h3 class="mt-2 text-2xl font-extrabold">{{ __('Recently approved items') }}</h3>
                            </div>
                            <a class="dc-button-secondary" href="{{ route('products.index') }}">{{ __('View Products') }}</a>
                        </div>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($featuredProducts as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
