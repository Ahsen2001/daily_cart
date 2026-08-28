@php
$title = $content["page_{$page}_title"];
$subtitle = $content["page_{$page}_subtitle"];
$body = $content["page_{$page}_body"];
$email = $content["page_{$page}_email"];
$phone = $content["page_{$page}_phone"];
$address = $content["page_{$page}_address"];
$ctaLabel = $content["page_{$page}_cta_label"];
$ctaUrl = $content["page_{$page}_cta_url"];
@endphp

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>{{ $title }} | DailyCart</title>

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
                href="{{ route('home') }}"
                class="min-w-0 shrink transition duration-300 hover:scale-[1.02] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20">

                <x-application-logo />
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-3">

                {{-- Back button --}}
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
                    class="hidden items-center gap-2 lg:flex"
                    aria-label="{{ __('Main navigation') }}">

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
                        href="{{ route('stores.index') }}">

                        {{ __('Stores') }}
                    </a>

                    <a
                        class="dc-button-secondary"
                        href="{{ route('pages.contact') }}">

                        {{ __('Contact') }}
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

    {{-- Main content --}}
    <main class="py-8 sm:py-10 lg:py-12">
        <div class="dc-container">

            {{-- Hero section --}}
            <section class="relative overflow-hidden rounded-3xl border border-green-100 bg-white p-6 shadow-soft sm:p-8">

                {{-- Decorative background --}}
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-brand-primary/5 blur-3xl"
                    aria-hidden="true">
                </div>

                <div
                    class="pointer-events-none absolute -bottom-28 left-1/3 h-48 w-48 rounded-full bg-brand-orange/5 blur-3xl"
                    aria-hidden="true">
                </div>

                <div class="relative z-10">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-dark">
                        {{ __('DailyCart') }}
                    </p>

                    <h1 class="mt-3 max-w-4xl text-2xl font-extrabold leading-tight tracking-tight text-brand-text sm:text-3xl lg:text-4xl">
                        {{ $title }}
                    </h1>

                    @if ($subtitle)
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-brand-text/70 sm:text-base">
                        {{ $subtitle }}
                    </p>
                    @endif

                    @if ($ctaLabel && $ctaUrl)
                    <a
                        class="dc-button mt-5"
                        href="{{ url($ctaUrl) }}">

                        {{ $ctaLabel }}
                    </a>
                    @endif
                </div>
            </section>

            {{-- Page content and details --}}
            <section class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">

                {{-- Main content --}}
                <article class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                    <div class="space-y-4 text-sm leading-7 text-brand-text/75 sm:text-base">
                        {!! nl2br(e($body)) !!}
                    </div>

                    {{-- Contact form --}}
                    @if ($page === 'contact')
                    <form
                        method="POST"
                        action="{{ route('pages.contact.store') }}"
                        class="mt-8 grid gap-4">

                        @csrf

                        @if (session('contact_status'))
                        <div
                            class="rounded-2xl border border-green-100 bg-green-50 p-4 text-sm font-medium text-green-700"
                            role="status">

                            {{ session('contact_status') }}
                        </div>
                        @endif

                        {{-- Name and email --}}
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="contact-name"
                                    class="mb-2 block text-sm font-semibold text-brand-text">

                                    {{ __('Name') }}
                                </label>

                                <input
                                    id="contact-name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Your name') }}"
                                    required>

                                @error('name')
                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="contact-email"
                                    class="mb-2 block text-sm font-semibold text-brand-text">

                                    {{ __('Email') }}
                                </label>

                                <input
                                    id="contact-email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                    placeholder="{{ __('Email address') }}"
                                    required>

                                @error('email')
                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label
                                for="contact-phone"
                                class="mb-2 block text-sm font-semibold text-brand-text">

                                {{ __('Phone') }}
                            </label>

                            <input
                                id="contact-phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="{{ __('Phone number') }}">

                            @error('phone')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label
                                for="contact-subject"
                                class="mb-2 block text-sm font-semibold text-brand-text">

                                {{ __('Subject') }}
                            </label>

                            <input
                                id="contact-subject"
                                name="subject"
                                value="{{ old('subject') }}"
                                class="w-full rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="{{ __('Message subject') }}"
                                required>

                            @error('subject')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div>
                            <label
                                for="contact-message"
                                class="mb-2 block text-sm font-semibold text-brand-text">

                                {{ __('Message') }}
                            </label>

                            <textarea
                                id="contact-message"
                                name="message"
                                rows="5"
                                class="w-full resize-y rounded-2xl border-brand-border bg-white text-sm focus:border-brand-primary focus:ring-brand-primary"
                                placeholder="{{ __('How can we help you?') }}"
                                required>{{ old('message') }}</textarea>

                            @error('message')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <button
                            class="dc-button w-full justify-center sm:w-fit sm:justify-self-start"
                            type="submit">

                            {{ __('Send Message') }}
                        </button>
                    </form>
                    @endif
                </article>

                {{-- Sidebar --}}
                <aside class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">

                    {{-- Contact details --}}
                    <div class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm">
                        <h2 class="text-base font-extrabold text-brand-dark">
                            {{ __('Details') }}
                        </h2>

                        <div class="mt-4 space-y-4 text-sm leading-6 text-brand-text/70">
                            @if ($email)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Email') }}
                                </p>

                                <a
                                    href="mailto:{{ $email }}"
                                    class="mt-1 block break-all font-medium transition hover:text-brand-primary">

                                    {{ $email }}
                                </a>
                            </div>
                            @endif

                            @if ($phone)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Phone') }}
                                </p>

                                <a
                                    href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
                                    class="mt-1 block font-medium transition hover:text-brand-primary">

                                    {{ $phone }}
                                </a>
                            </div>
                            @endif

                            @if ($address)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-brand-text/50">
                                    {{ __('Address') }}
                                </p>

                                <p class="mt-1 font-medium">
                                    {{ $address }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Offer note --}}
                    @if ($page === 'offers')
                    <div class="rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-4 shadow-sm">
                        <h2 class="text-sm font-extrabold text-brand-dark">
                            {{ __('Offer Note') }}
                        </h2>

                        <p class="mt-2 text-xs leading-6 text-brand-text/70 sm:text-sm">
                            {{ __('Offer availability can change by stock, vendor approval, and promotion validity.') }}
                        </p>
                    </div>
                    @endif
                </aside>
            </section>

            @if ($page === 'about')
            <section class="mt-8 space-y-8" aria-label="{{ __('About DailyCart') }}">
                <div class="grid gap-6 lg:grid-cols-2">
                    <article class="relative overflow-hidden rounded-3xl border border-green-100 bg-gradient-to-br from-brand-dark to-brand-primary p-6 text-white shadow-soft sm:p-8">
                        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-100">{{ __('Our mission') }}</p>
                            <h2 class="mt-3 text-2xl font-extrabold leading-tight text-white sm:text-3xl">{{ __('Daily essentials within easier reach.') }}</h2>
                            <p class="mt-4 max-w-xl text-sm leading-7 text-white/85 sm:text-base">
                                {{ __('Our mission is to make daily essentials accessible through fast, reliable local delivery while helping trusted local businesses serve more households.') }}
                            </p>
                        </div>
                    </article>

                    <article class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="dc-page-eyebrow">{{ __('Our story') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Built for everyday shopping in Sri Lanka') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-brand-text/70 sm:text-base">
                            {{ __('DailyCart began with a simple idea: bring customers, local vendors, and delivery riders together in one dependable marketplace. From our roots in Batticaloa, we are building a practical shopping experience centred on local availability, clear order updates, and reliable support.') }}
                        </p>
                    </article>
                </div>

                <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                    <div class="max-w-2xl">
                        <p class="dc-page-eyebrow">{{ __('How DailyCart works') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('From local store to your door') }}</h2>
                    </div>

                    <ol class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                        ['Browse', 'Explore products and approved local stores.'],
                        ['Build your cart', 'Choose the items and quantities you need.'],
                        ['Checkout', 'Select your delivery and payment options.'],
                        ['Track delivery', 'Follow your order until it reaches you.'],
                        ] as $index => [$step, $description])
                        <li class="rounded-2xl border border-green-100 bg-brand-light/60 p-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-primary text-sm font-extrabold text-white">{{ $index + 1 }}</span>
                            <h3 class="mt-4 font-extrabold text-brand-text">{{ __($step) }}</h3>
                            <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __($description) }}</p>
                        </li>
                        @endforeach
                    </ol>
                </section>

                <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="dc-page-eyebrow">{{ __('What you can shop') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Essentials for every day') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-brand-text/70">{{ __('Browse useful products from approved vendors across the DailyCart marketplace.') }}</p>
                        <div class="mt-6 flex flex-wrap gap-2.5">
                            @foreach (['Groceries', 'Vegetables', 'Fruits', 'Bakery', 'Household goods', 'Pharmacy products', 'Beverages', 'Personal care'] as $category)
                            <span class="rounded-full border border-green-100 bg-green-50 px-4 py-2 text-sm font-semibold text-brand-dark">{{ __($category) }}</span>
                            @endforeach
                        </div>
                        <a class="dc-button-secondary mt-6" href="{{ route('categories.index') }}">{{ __('Browse categories') }}</a>
                    </section>

                    <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="dc-page-eyebrow">{{ __('Why choose DailyCart') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Shopping built around trust') }}</h2>
                        <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach (['Approved local vendors', 'Secure checkout', 'Reliable delivery', 'Order tracking', 'Multiple payment methods', 'Customer support'] as $benefit)
                            <li class="flex items-start gap-3 rounded-2xl bg-brand-light/60 p-4 text-sm font-semibold text-brand-text">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-primary text-xs text-white" aria-hidden="true">✓</span>
                                <span>{{ __($benefit) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                    <div class="max-w-2xl">
                        <p class="dc-page-eyebrow">{{ __('Our marketplace') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Powered by people working together') }}</h2>
                    </div>
                    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                        ['Customers', 'Discover stores, shop essentials, and track every order.'],
                        ['Vendors', 'Manage storefronts, products, promotions, and fulfilment.'],
                        ['Riders', 'Coordinate deliveries and keep customers informed.'],
                        ['DailyCart team', 'Support the marketplace, safety, and service quality.'],
                        ] as [$participant, $description])
                        <article class="rounded-2xl border border-brand-border p-5">
                            <h3 class="font-extrabold text-brand-dark">{{ __($participant) }}</h3>
                            <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ __($description) }}</p>
                        </article>
                        @endforeach
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-2">
                    <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="dc-page-eyebrow">{{ __('Service area') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('Growing from Batticaloa') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-brand-text/70">
                            {{ __('DailyCart serves Batticaloa and other locations enabled through our active delivery network. Product and delivery availability may vary by vendor, address, and configured delivery zone. Confirm availability for your location during checkout.') }}
                        </p>
                    </section>

                    <section class="rounded-3xl border border-brand-border bg-white p-6 shadow-sm sm:p-8">
                        <p class="dc-page-eyebrow">{{ __('Our values') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-brand-text">{{ __('What guides DailyCart') }}</h2>
                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm font-semibold text-brand-text sm:grid-cols-3">
                            @foreach (['Reliability', 'Customer care', 'Fair marketplace', 'Product quality', 'Data privacy', 'Local business support'] as $value)
                            <div class="rounded-2xl bg-brand-light/70 px-4 py-3 text-center">{{ __($value) }}</div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <section class="overflow-hidden rounded-3xl bg-brand-text p-6 text-white shadow-soft sm:p-8">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-200">{{ __('Join DailyCart') }}</p>
                            <h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ __('Shop, sell, or deliver with us') }}</h2>
                            <p class="mt-3 text-sm leading-7 text-white/70">{{ __('Choose the DailyCart experience that fits you and become part of a growing local marketplace.') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a class="dc-button" href="{{ route('products.index') }}">{{ __('Start Shopping') }}</a>
                            <a class="dc-button-secondary" href="{{ route('stores.index') }}">{{ __('Browse Stores') }}</a>
                            <a class="dc-button-secondary" href="{{ route('vendor.register') }}">{{ __('Become a Vendor') }}</a>
                            <a class="dc-button-secondary" href="{{ route('rider.register') }}">{{ __('Become a Rider') }}</a>
                        </div>
                    </div>
                </section>

                <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 rounded-3xl border border-brand-border bg-white px-6 py-5 text-sm font-semibold text-brand-dark shadow-sm" aria-label="{{ __('DailyCart policies') }}">
                    <span class="text-brand-text/55">{{ __('Learn about your rights and responsibilities:') }}</span>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.refund-policy') }}">{{ __('Refund Policy') }}</a>
                    <a class="underline decoration-green-200 underline-offset-4 hover:text-brand-primary" href="{{ route('pages.terms-and-conditions') }}">{{ __('Terms and Conditions') }}</a>
                </nav>
            </section>
            @endif

            {{-- Offers --}}
            @if ($page === 'offers')
            <div class="mx-auto mt-5 max-w-6xl">
                <x-offers-today
                    :promotions="$promotions ?? collect()"
                    :contained="true" />
            </div>
            @endif
        </div>
    </main>

    <x-footer />
</body>

</html>