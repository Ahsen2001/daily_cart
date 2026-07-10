<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $selectedCategory?->name ?? __('Products') }} - DailyCart</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-light font-sans text-brand-text">
        <header class="sticky top-0 z-40 border-b border-green-100 bg-white/90 backdrop-blur-xl">
            <div class="dc-container flex h-20 items-center justify-between">
                <a href="{{ url('/') }}" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <nav class="hidden items-center gap-3 md:flex">
                    <a class="dc-button-secondary" href="{{ route('categories.index') }}">{{ __('Categories') }}</a>
                    @auth
                        <a class="dc-button" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    @else
                        <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="bg-white py-12 sm:py-16">
                <div class="dc-container">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="font-semibold text-brand-dark">{{ __('Available Products') }}</p>
                            <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">
                                {{ $selectedCategory?->name ?? __('All Approved Products') }}
                            </h1>
                        </div>
                        <form method="GET" action="{{ route('products.index') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto] lg:min-w-[680px]">
                            <x-search-bar name="search" placeholder="Search products or brands..." :value="request('search')" />
                            <select name="category" class="border-gray-300 rounded-md shadow-sm">
                                <option value="">{{ __('All categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <button class="dc-button" type="submit">{{ __('Search') }}</button>
                        </form>
                    </div>

                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($products as $product)
                            @php
                                $price = $product->discount_price ?: $product->price;
                            @endphp
                            <article class="group overflow-hidden rounded-3xl border border-green-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-soft">
                                <div class="aspect-[4/3] overflow-hidden bg-brand-light">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                    @else
                                        <div class="flex h-full items-center justify-center">
                                            <x-application-logo :show-text="false" class="opacity-70" />
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-4 p-5">
                                    <div>
                                        <p class="text-xs font-medium text-brand-dark">{{ $product->category?->name }}</p>
                                        <h2 class="mt-1 line-clamp-2 text-base font-bold text-brand-text">{{ $product->name }}</h2>
                                        @if ($product->brand)
                                            <p class="mt-1 text-xs text-brand-text/55">{{ $product->brand }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-bold text-brand-dark">{{ \App\Services\CurrencyService::formatLkr($price) }}</p>
                                        <a class="dc-button-secondary px-4 py-2" href="{{ route('login') }}">{{ __('Buy') }}</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="dc-card sm:col-span-2 lg:col-span-3 xl:col-span-4">
                                <p class="text-sm text-brand-text/70">{{ __('No approved products are available for this category yet.') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">{{ $products->links() }}</div>
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
