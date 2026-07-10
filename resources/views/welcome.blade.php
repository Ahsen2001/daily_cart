@php
    $featuredProducts = \App\Models\Product::query()
        ->visibleToCustomers()
        ->with(['category', 'vendor'])
        ->latest()
        ->limit(4)
        ->get();
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
        <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 backdrop-blur-xl">
            <div class="dc-container flex h-20 items-center justify-between">
                <a href="/" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <nav class="hidden items-center gap-3 md:flex">
                    <a class="text-sm font-semibold text-brand-text/70 transition hover:text-brand-dark" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                    <a class="text-sm font-semibold text-brand-text/70 transition hover:text-brand-dark" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    <a class="text-sm font-semibold text-brand-text/70 transition hover:text-brand-dark" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                    <a class="dc-button-secondary" href="{{ route('login') }}">{{ __('Login') }}</a>
                    <a class="dc-button" href="{{ route('register') }}">{{ __('Start Shopping') }}</a>
                </nav>
            </div>
        </header>

        <main>
            <section class="dc-container grid min-h-[72vh] items-center gap-10 py-14 lg:grid-cols-[1.05fr_.95fr]">
                <div class="animate-fade-up">
                    <x-notification-badge>{{ __('Fresh Daily Essentials') }}</x-notification-badge>
                    <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-tight text-brand-text sm:text-5xl lg:text-6xl">
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
                    <div class="rounded-[2rem] bg-white p-5 shadow-soft">
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
                        @forelse ($featuredProducts as $product)
                            <x-product-card :product="$product" />
                        @empty
                            @foreach ([
                                ['name' => 'Grocery', 'slug' => 'grocery', 'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80'],
                                ['name' => 'Vegetables', 'slug' => 'vegetables', 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=900&q=80'],
                                ['name' => 'Bakery', 'slug' => 'bakery', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=80'],
                                ['name' => 'Pharmacy', 'slug' => 'pharmacy', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=900&q=80'],
                            ] as $category)
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
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
