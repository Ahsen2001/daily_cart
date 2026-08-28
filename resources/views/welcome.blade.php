@php
$isAuthenticatedCustomer = auth()->check()
&& auth()->user()->hasPrimaryRole('Customer');

$homepageCategories = [
['name' => 'Grocery', 'slug' => 'grocery', 'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=80'],
['name' => 'Vegetables', 'slug' => 'vegetables', 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=900&q=80'],
['name' => 'Bakery', 'slug' => 'bakery', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=900&q=80'],
['name' => 'Pharmacy', 'slug' => 'pharmacy', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=900&q=80'],
['name' => 'Beverages', 'slug' => 'beverages', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=900&q=80'],
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
    <style>
        /* 3D Transforms and Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes float-slow {

            0%,
            100% {
                transform: translateY(0px) rotateZ(-5deg);
            }

            50% {
                transform: translateY(-15px) rotateZ(-5deg);
            }
        }

        @keyframes glow-pulse {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(21, 128, 61, 0.3), 0 20px 50px rgba(11, 93, 42, 0.12);
            }

            50% {
                box-shadow: 0 0 40px rgba(21, 128, 61, 0.5), 0 20px 50px rgba(11, 93, 42, 0.2);
            }
        }

        @keyframes rotate-3d {
            0% {
                transform: rotateX(0deg) rotateY(0deg);
            }

            100% {
                transform: rotateX(360deg) rotateY(360deg);
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        @keyframes blob-animation {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        .perspective-3d {
            perspective: 1200px;
        }

        .card-3d {
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.23, 1, 0.320, 1);
        }

        .card-3d:hover {
            transform: rotateX(10deg) rotateY(-10deg) translateZ(30px);
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        .float-slow-animation {
            animation: float-slow 8s ease-in-out infinite;
        }

        .glow-pulse-animation {
            animation: glow-pulse 3s ease-in-out infinite;
        }

        .slide-up-animation {
            animation: slide-up 0.8s ease-out;
        }

        .scale-in-animation {
            animation: scale-in 0.6s cubic-bezier(0.23, 1, 0.320, 1);
        }

        .shimmer-animation {
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%);
            background-size: 1000px 100%;
            animation: shimmer 3s infinite;
        }

        .blob-animation {
            animation: blob-animation 7s infinite;
        }

        .gradient-text {
            background: linear-gradient(135deg, #15803D 0%, #059669 50%, #10B981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-border {
            position: relative;
            background: white;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #15803D 0%, #059669 50%, #10B981 100%);
            border-radius: inherit;
            z-index: -1;
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .parallax-element {
            transform: translateZ(0);
        }

        /* Scroll animation trigger */
        .scroll-animation {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.23, 1, 0.320, 1);
        }

        .scroll-animation.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        /* 3D Button Effects */
        .button-3d {
            position: relative;
            transform-style: preserve-3d;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1);
        }

        .button-3d:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(21, 128, 61, 0.3), 0 0 20px rgba(21, 128, 61, 0.2);
        }

        .button-3d:active {
            transform: translateY(-1px);
        }

        /* Animated Background Blobs */
        .blob {
            position: absolute;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(40px);
            opacity: 0.5;
        }

        .blob-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, rgba(21, 128, 61, 0.1), rgba(16, 185, 129, 0.1));
            top: -100px;
            left: -50px;
            animation: blob-animation 7s infinite;
        }

        .blob-2 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(194, 65, 12, 0.08), rgba(251, 146, 60, 0.08));
            bottom: -50px;
            right: -50px;
            animation: blob-animation 7s infinite 2s;
        }

        .blob-3 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(99, 102, 241, 0.05));
            bottom: 100px;
            left: 50%;
            animation: blob-animation 7s infinite 4s;
        }

        /* Stagger animations for list items */
        .stagger-item {
            animation: slide-up 0.8s ease-out backwards;
        }

        .stagger-item:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stagger-item:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stagger-item:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stagger-item:nth-child(4) {
            animation-delay: 0.4s;
        }

        .stagger-item:nth-child(5) {
            animation-delay: 0.5s;
        }

        .stagger-item:nth-child(6) {
            animation-delay: 0.6s;
        }

        /* Card stagger for grids */
        .grid>* {
            animation: scale-in 0.6s cubic-bezier(0.23, 1, 0.320, 1) backwards;
        }

        .grid> :nth-child(1) {
            animation-delay: 0.05s;
        }

        .grid> :nth-child(2) {
            animation-delay: 0.1s;
        }

        .grid> :nth-child(3) {
            animation-delay: 0.15s;
        }

        .grid> :nth-child(4) {
            animation-delay: 0.2s;
        }

        .grid> :nth-child(5) {
            animation-delay: 0.25s;
        }

        .grid> :nth-child(6) {
            animation-delay: 0.3s;
        }
    </style>
</head>

<body class="bg-white font-sans text-brand-text overflow-x-hidden">
    <header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-brand-border/50 bg-white/80 backdrop-blur-xl transition-all duration-300">
        <div class="dc-container flex h-20 items-center justify-between gap-4">
            <a href="/" class="transition hover:scale-[1.02]"><x-application-logo /></a>
            <div class="hidden items-center gap-3 md:flex">
                <nav class="flex items-center rounded-full border border-brand-border bg-brand-light/70 p-1" aria-label="{{ __('Primary navigation') }}">
                    <a class="dc-public-nav-link" href="{{ route('pages.about') }}">{{ __('About') }}</a>
                    <a class="dc-public-nav-link" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    <a class="dc-public-nav-link" href="{{ route('stores.index') }}">{{ __('Stores') }}</a>
                    <a class="dc-public-nav-link" href="{{ route('pages.contact') }}">{{ __('Contact') }}</a>
                </nav>
                @if ($isAuthenticatedCustomer)
                <a class="dc-button-secondary button-3d px-5" href="{{ route('customer.dashboard') }}">
                    {{ __('Dashboard') }}
                </a>
                <a class="dc-button button-3d px-6" href="{{ route('customer.products.index') }}">
                    {{ __('Shop Products') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                    </svg>
                </a>
                @else
                <a class="dc-button-secondary button-3d px-5" href="{{ route('login') }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm4 12a7 7 0 0 0-14 0" />
                    </svg>
                    {{ __('Login') }}
                </a>
                <a class="dc-button button-3d px-6" href="{{ route('register') }}">
                    {{ __('Start Shopping') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                    </svg>
                </a>
                @endif
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
                    @if ($isAuthenticatedCustomer)
                    <a class="dc-button-secondary" href="{{ route('customer.dashboard') }}">{{ __('Dashboard') }}</a>
                    <a class="dc-button" href="{{ route('customer.products.index') }}">{{ __('Shop Products') }}</a>
                    @else
                    <a class="dc-button-secondary" href="{{ route('login') }}">{{ __('Login') }}</a>
                    <a class="dc-button" href="{{ route('register') }}">{{ __('Start Shopping') }}</a>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <main class="relative overflow-hidden">
        {{-- Animated Background Blobs --}}
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>

        {{-- Hero Section with 3D Effects --}}
        <section class="relative dc-container grid min-h-[72vh] items-center gap-10 py-10 sm:py-14 lg:grid-cols-[1.05fr_.95fr] perspective-3d">
            <div class="stagger-item space-y-6">
                <x-notification-badge>{{ __('Fresh Daily Essentials') }}</x-notification-badge>
                <h1 class="max-w-3xl text-3xl font-extrabold leading-[1.08] tracking-tight gradient-text sm:text-4xl lg:text-5xl">
                    Smart Shopping and Daily Essentials Delivery for Your Home.
                </h1>
                <p class="max-w-2xl text-lg leading-8 text-brand-text/70">
                    DailyCart brings groceries, vegetables, fruits, household items, bakery goods, pharmacy products, and more into one clean delivery platform.
                </p>
                <div class="flex flex-wrap gap-3">
                    @if ($isAuthenticatedCustomer)
                    <a class="dc-button button-3d" href="{{ route('customer.products.index') }}">{{ __('Shop Products') }}</a>
                    <a class="dc-button-secondary button-3d" href="{{ route('stores.index') }}">{{ __('Browse Stores') }}</a>
                    @else
                    <a class="dc-button button-3d" href="{{ route('register') }}">{{ __('Create Customer Account') }}</a>
                    <a class="dc-button-secondary button-3d" href="{{ route('vendor.register') }}">{{ __('Become a Vendor') }}</a>
                    @endif
                </div>
            </div>

            <div class="relative float-animation perspective-3d">
                <div class="card-3d rounded-[2rem] border border-brand-border/50 bg-white p-5 shadow-soft glow-pulse-animation">
                    <div class="shimmer-animation rounded-3xl">
                        <img src="{{ asset('images/logo.png') }}" alt="DailyCart logo" class="mx-auto h-56 w-56 rounded-3xl object-contain">
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="stagger-item rounded-3xl bg-gradient-to-br from-brand-light to-emerald-50 p-4 text-center transition-all duration-300 hover:shadow-md hover:scale-105">
                            <p class="text-2xl font-bold text-brand-dark">30+</p>
                            <p class="text-xs text-brand-text/60">Categories</p>
                        </div>
                        <div class="stagger-item rounded-3xl bg-gradient-to-br from-brand-light to-emerald-50 p-4 text-center transition-all duration-300 hover:shadow-md hover:scale-105">
                            <p class="text-2xl font-bold text-brand-dark">LKR</p>
                            <p class="text-xs text-brand-text/60">Only</p>
                        </div>
                        <div class="stagger-item rounded-3xl bg-gradient-to-br from-brand-light to-emerald-50 p-4 text-center transition-all duration-300 hover:shadow-md hover:scale-105">
                            <p class="text-2xl font-bold text-brand-dark">30m</p>
                            <p class="text-xs text-brand-text/60">Min schedule</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Offers Section with 3D Effects --}}
        @if ($todayOffers->isNotEmpty())
        <section
            class="scroll-animation mx-auto my-8 max-w-6xl overflow-hidden rounded-3xl border border-brand-border bg-gradient-to-br from-brand-dark via-brand-primary to-emerald-600 py-8 text-white sm:py-10 shadow-2xl"
            aria-labelledby="offers-today-title">
            <div class="dc-container">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="stagger-item space-y-3">
                        <p class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] ring-1 ring-white/20">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-amber-300" aria-hidden="true"></span>
                            {{ __('Live savings') }}
                        </p>
                        <h2 id="offers-today-title" class="text-xl font-extrabold sm:text-2xl">{{ __('Offers Today') }}</h2>
                        <p class="max-w-2xl text-sm leading-7 text-white/80 sm:text-base">{{ __('Fresh, active offers announced by DailyCart and approved vendors.') }}</p>
                    </div>
                    <a class="button-3d inline-flex min-h-10 items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-brand-dark shadow-card" href="{{ route('pages.offers') }}">
                        {{ __('View all offers') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                        </svg>
                    </a>
                </div>

                <div class="mx-auto mt-6 grid max-w-5xl grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($todayOffers as $promotion)
                    @php
                    $offerProduct = $promotion->target_type === 'product' ? $promotion->targetProduct : null;
                    $offerImage = $promotion->banner_image
                    ? \Illuminate\Support\Facades\Storage::url($promotion->banner_image)
                    : $offerProduct?->display_image_url;
                    $offerUrl = $offerProduct
                    ? route('products.show', ['product' => $offerProduct->slug, 'promotion' => $promotion->id])
                    : route('pages.offers');
                    $discountLabel = $promotion->discount_type === 'percentage'
                    ? rtrim(rtrim(number_format((float) $promotion->discount_value, 2), '0'), '.').'% OFF'
                    : \App\Services\CurrencyService::formatLkr($promotion->discount_value).' OFF';
                    $offerBasePrice = $offerProduct ? (float) ($offerProduct->discount_price ?: $offerProduct->price) : null;
                    $offerPrice = $offerBasePrice !== null ? $promotion->priceFor($offerBasePrice) : null;
                    @endphp

                    <article class="card-3d group overflow-hidden rounded-2xl bg-white text-brand-text shadow-card transition-all duration-300">
                        <a href="{{ $offerUrl }}" class="block h-full">
                            <div class="relative aspect-[16/8] overflow-hidden bg-brand-light perspective-3d">
                                @if ($offerImage)
                                <img src="{{ $offerImage }}" alt="{{ $promotion->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" loading="lazy" decoding="async">
                                @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-emerald-50 to-amber-50">
                                    <img src="{{ asset('images/logo.png') }}" alt="" class="h-10 w-10 object-contain opacity-80">
                                </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/20 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                <span class="absolute left-2.5 top-2.5 rounded-full bg-brand-orange px-2.5 py-1 text-[10px] font-extrabold text-white shadow-card">
                                    {{ $discountLabel }}
                                </span>
                                <span class="absolute right-2.5 top-2.5 max-w-[45%] truncate rounded-full bg-white/90 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-brand-dark backdrop-blur">
                                    {{ str_replace('_', ' ', $promotion->promotion_type) }}
                                </span>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        @if ($offerProduct)
                                        <p class="text-xs font-bold uppercase tracking-wide text-brand-dark">{{ $offerProduct->category?->name }}</p>
                                        @elseif ($promotion->vendor)
                                        <p class="text-xs font-bold uppercase tracking-wide text-brand-dark">{{ $promotion->vendor->store_name }}</p>
                                        @else
                                        <p class="text-xs font-bold uppercase tracking-wide text-brand-dark">{{ __('DailyCart special') }}</p>
                                        @endif
                                        <h3 class="mt-1.5 line-clamp-2 text-sm font-extrabold leading-5 sm:text-base">{{ $promotion->title }}</h3>
                                    </div>
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-light text-brand-dark transition-all duration-300 group-hover:bg-brand-primary group-hover:text-white group-hover:scale-110" aria-hidden="true">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                                        </svg>
                                    </span>
                                </div>
                                @if ($offerProduct)
                                <div class="mt-3 flex items-center justify-between border-t border-brand-border pt-3">
                                    <p class="line-clamp-1 text-sm font-semibold text-brand-text/70">{{ $offerProduct->name }}</p>
                                    <div class="shrink-0 pl-3 text-right">
                                        <p class="font-extrabold text-brand-dark">{{ \App\Services\CurrencyService::formatLkr($offerPrice) }}</p>
                                        <p class="text-xs text-brand-text/45 line-through">{{ \App\Services\CurrencyService::formatLkr($offerBasePrice) }}</p>
                                    </div>
                                </div>
                                @elseif ($promotion->description)
                                <p class="mt-4 line-clamp-2 text-sm leading-6 text-brand-text/65">{{ $promotion->description }}</p>
                                @endif
                                <p class="mt-3 text-[10px] font-semibold text-brand-text/55 sm:text-xs">{{ __('Ends :date', ['date' => $promotion->ends_at->format('M d, Y · g:i A')]) }}</p>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Popular Products Section --}}
        <section class="scroll-animation bg-white py-14">
            <div class="dc-container">
                <div class="stagger-item flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-8">
                    <div>
                        <p class="font-semibold text-brand-dark">{{ __('Popular Products') }}</p>
                        <h2 class="mt-2 text-3xl font-extrabold gradient-text">{{ __('Fresh picks for today') }}</h2>
                    </div>
                    <a class="dc-button-secondary button-3d" href="{{ route('categories.index') }}">{{ __('Browse All') }}</a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($homepageCategories as $category)
                    <a
                        href="{{ route('products.index', ['category' => $category['slug']]) }}"
                        class="stagger-item dc-card group card-3d block overflow-hidden p-0 text-center">

                        <div class="aspect-[4/3] overflow-hidden bg-brand-light perspective-3d">
                            <img
                                src="{{ $category['image'] }}"
                                alt="{{ $category['name'] }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                                loading="lazy"
                                decoding="async">
                        </div>

                        <div class="p-4 sm:p-5">
                            <h3 class="line-clamp-1 text-sm font-bold text-brand-dark sm:text-base">
                                {{ $category['name'] }}
                            </h3>

                            <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-brand-text/60 sm:text-sm">
                                {{ __('View approved products in this category.') }}
                            </p>
                        </div>
                    </a>
                    @endforeach
                </div>

                @if ($featuredStores->isNotEmpty() || $popularStores->isNotEmpty())
                <div class="mt-8 space-y-10">
                    {{-- Featured Stores --}}
                    @if ($featuredStores->isNotEmpty())
                    <section aria-labelledby="featured-stores-title" class="scroll-animation">
                        <div class="stagger-item flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-8">
                            <div>
                                <p class="font-semibold text-brand-dark">
                                    {{ __('Featured Stores') }}
                                </p>

                                <h3
                                    id="featured-stores-title"
                                    class="mt-2 text-2xl font-extrabold gradient-text">

                                    {{ __('Shop trusted local vendors') }}
                                </h3>
                            </div>

                            <a
                                class="dc-button-secondary button-3d w-fit"
                                href="{{ route('stores.index', ['featured' => 1]) }}">

                                {{ __('View All Stores') }}
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
                            @foreach ($featuredStores as $store)
                            <x-store-card :store="$store" />
                            @endforeach
                        </div>

                    </section>
                    @endif
                </div>

                {{-- Popular Stores --}}
                @if ($popularStores->isNotEmpty())
                <div class="mt-8 space-y-10">
                    <section aria-labelledby="popular-stores-title" class="scroll-animation">
                        <div class="stagger-item flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-8">
                            <div>
                                <p class="font-semibold text-brand-dark">
                                    {{ __('Popular Stores') }}
                                </p>

                                <h3
                                    id="popular-stores-title"
                                    class="mt-2 text-2xl font-extrabold gradient-text">

                                    {{ __('Discover more DailyCart vendors') }}
                                </h3>
                            </div>

                            <a
                                class="dc-button-secondary button-3d w-fit"
                                href="{{ route('stores.index') }}">

                                {{ __('Browse Stores') }}
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
                            @foreach ($popularStores as $store)
                            <x-store-card :store="$store" />
                            @endforeach

                        </div>

                    </section>

                </div>
                @endif

                @endif

                @if ($featuredProducts->isNotEmpty())
                <div class="stagger-item mt-12 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between scroll-animation">
                    <div>
                        <p class="font-semibold text-brand-dark">{{ __('Featured Products') }}</p>
                        <h3 class="mt-2 text-2xl font-extrabold gradient-text">{{ __('Recently approved items') }}</h3>
                    </div>
                    <a class="dc-button-secondary button-3d" href="{{ route('products.index') }}">{{ __('View Products') }}</a>
                </div>
                <div class="mt-6 grid auto-rows-fr grid-cols-2 items-stretch gap-5 sm:grid-cols-3 lg:grid-cols-5">

                    @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                    @endforeach
                </div>
                @endif
            </div>
        </section>
    </main>

    <x-footer />

    <script>
        // Scroll animation trigger
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animation').forEach(el => {
            observer.observe(el);
        });

        // Parallax effect on mouse move
        document.addEventListener('mousemove', (e) => {
            const parallaxElements = document.querySelectorAll('.parallax-element');
            const x = (window.innerWidth - e.clientX * 2) / 100;
            const y = (window.innerHeight - e.clientY * 2) / 100;

            parallaxElements.forEach(el => {
                el.style.transform = `translateX(${x}px) translateY(${y}px)`;
            });
        });
    </script>
</body>

</html>