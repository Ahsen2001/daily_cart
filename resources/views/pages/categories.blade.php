<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Categories') }} - DailyCart</title>
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
                    @auth
                        <a class="dc-button-secondary" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    @else
                        <a class="dc-button-secondary" href="{{ route('login') }}">{{ __('Login') }}</a>
                        <a class="dc-button" href="{{ route('register') }}">{{ __('Start Shopping') }}</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="bg-white py-12 sm:py-16">
                <div class="dc-container">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="font-semibold text-brand-dark">{{ __('DailyCart Marketplace') }}</p>
                            <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ __('Browse Categories') }}</h1>
                        </div>
                        <a class="dc-button-secondary" href="{{ url('/') }}">{{ __('Back Home') }}</a>
                    </div>

                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($categories as $category)
                            <article class="group overflow-hidden rounded-3xl border border-green-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-soft">
                                <div class="aspect-[4/3] overflow-hidden bg-brand-light">
                                    <img
                                        src="{{ $category->display_image_url }}"
                                        alt="{{ $category->name }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                </div>
                                <div class="space-y-3 p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <h2 class="text-lg font-bold text-brand-text">{{ $category->name }}</h2>
                                        <span class="shrink-0 rounded-full bg-brand-light px-3 py-1 text-xs font-bold text-brand-dark">
                                            {{ $category->available_products_count }}
                                        </span>
                                    </div>
                                    <p class="line-clamp-3 text-sm leading-6 text-brand-text/65">
                                        {{ $category->description ?: __('Fresh DailyCart essentials selected for this category.') }}
                                    </p>
                                </div>
                            </article>
                        @empty
                            <div class="dc-card sm:col-span-2 lg:col-span-3 xl:col-span-4">
                                <p class="text-sm text-brand-text/70">{{ __('No active categories are available yet.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
