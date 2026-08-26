<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>{{ $store->vendor->store_name }} - DailyCart</title>

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

                {{-- All Stores --}}
                <a
                    class="hidden dc-button-secondary sm:inline-flex"
                    href="{{ route('stores.index') }}">

                    {{ __('All Stores') }}
                </a>
            </div>
        </div>
    </header>

    <main class="pb-12">

        {{-- Store hero --}}
        <section class="relative isolate overflow-hidden bg-brand-dark">

            {{-- Cover image --}}
            <img
                src="{{ $store->cover_image_url }}"
                alt=""
                class="absolute inset-0 h-full w-full object-cover opacity-30"
                aria-hidden="true">

            {{-- Overlay --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-brand-dark/95 via-brand-dark/75 to-brand-primary/45"
                aria-hidden="true">
            </div>

            <div
                class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"
                aria-hidden="true">
            </div>

            <div class="dc-container relative py-10 sm:py-12 lg:py-14">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end">

                    {{-- Store logo --}}
                    <img
                        src="{{ $store->logo_url }}"
                        alt="{{ $store->vendor->store_name }}"
                        class="h-24 w-24 shrink-0 rounded-2xl border-4 border-white bg-white object-cover shadow-lift sm:h-28 sm:w-28">

                    {{-- Store information --}}
                    <div class="min-w-0 text-white">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">
                            {{ __('DailyCart store') }}
                        </p>

                        <h1 class="mt-2 text-2xl font-extrabold leading-tight tracking-tight sm:text-3xl lg:text-4xl">
                            {{ $store->vendor->store_name }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/80">
                            {{
                                $store->description
                                ?: __('An approved DailyCart vendor storefront.')
                            }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="dc-container relative -mt-5 space-y-8">

            {{-- Store summary --}}
            <section class="rounded-3xl border border-brand-border bg-white p-5 shadow-card">
                <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-6 lg:items-end">

                    {{-- Rating --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-brand-text/50 sm:text-xs">
                            {{ __('Rating') }}
                        </p>

                        <p class="mt-1 text-base font-extrabold text-brand-dark sm:text-lg">
                            {{ $rating ? number_format($rating, 1) : __('New') }}

                            <span class="text-brand-orange" aria-hidden="true">
                                &#9733;
                            </span>
                        </p>
                    </div>

                    {{-- Delivery --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-brand-text/50 sm:text-xs">
                            {{ __('Delivery') }}
                        </p>

                        <p class="mt-1 text-sm font-bold text-brand-dark">
                            {{ $store->delivery_estimate ?: '-' }}
                        </p>
                    </div>

                    {{-- Minimum order --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-brand-text/50 sm:text-xs">
                            {{ __('Minimum order') }}
                        </p>

                        <p class="mt-1 text-sm font-bold text-brand-dark">
                            {{
                                $store->minimum_order
                                ? \App\Services\CurrencyService::formatLkr(
                                    $store->minimum_order
                                )
                                : __('No minimum')
                            }}
                        </p>
                    </div>

                    {{-- Opening hours --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-brand-text/50 sm:text-xs">
                            {{ __('Opening hours') }}
                        </p>

                        <p class="mt-1 text-sm font-bold leading-5 text-brand-dark">
                            {{
                                data_get(
                                    $store->opening_hours,
                                    'summary',
                                    __('Contact store')
                                )
                            }}
                        </p>
                    </div>

                    {{-- Followers --}}
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-brand-text/50 sm:text-xs">
                            {{ __('Followers') }}
                        </p>

                        <p class="mt-1 text-sm font-bold text-brand-dark">
                            {{ number_format($store->followers_count) }}
                        </p>
                    </div>

                    {{-- Follow button --}}
                    @auth
                        @if (Auth::user()->hasPrimaryRole('Customer'))
                            <div class="col-span-2 flex sm:col-span-1 lg:justify-end">
                                <form
                                    method="POST"
                                    action="{{
                                        $isFollowing
                                            ? route(
                                                'customer.stores.unfollow',
                                                $store
                                            )
                                            : route(
                                                'customer.stores.follow',
                                                $store
                                            )
                                    }}">

                                    @csrf

                                    @if ($isFollowing)
                                        @method('DELETE')
                                    @endif

                                    <button
                                        class="{{ $isFollowing ? 'dc-button-secondary' : 'dc-button' }} whitespace-nowrap"
                                        type="submit">

                                        {{
                                            $isFollowing
                                                ? __('Following')
                                                : __('Follow store')
                                        }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </section>

            {{-- Store banners --}}
            @if ($store->banners->isNotEmpty())
                <section
                    class="grid auto-rows-fr gap-4 md:grid-cols-2 lg:grid-cols-3"
                    aria-label="{{ __('Store banners') }}">

                    @foreach ($store->banners as $banner)
                        <a
                            href="{{ $banner->link_url ?: '#' }}"
                            class="group flex h-full flex-col overflow-hidden rounded-2xl border border-brand-border bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-soft">

                            <div class="aspect-[16/7] overflow-hidden bg-brand-light">
                                <img
                                    src="{{ $banner->image_url }}"
                                    alt="{{ $banner->title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async">
                            </div>

                            <p class="flex-1 p-4 text-sm font-bold text-brand-dark">
                                {{ $banner->title }}
                            </p>
                        </a>
                    @endforeach
                </section>
            @endif

            {{-- Store products --}}
            <section aria-labelledby="store-products-title">

                {{-- Heading --}}
                <div>
                    <p class="text-sm font-semibold text-brand-dark">
                        {{ __('Store products') }}
                    </p>

                    <h2
                        id="store-products-title"
                        class="mt-1 text-xl font-extrabold tracking-tight sm:text-2xl">

                        {{ __('Shop from :store', [
                            'store' => $store->vendor->store_name
                        ]) }}
                    </h2>
                </div>

                {{-- Category labels --}}
                @if ($categories->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            <span class="rounded-full border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-brand-text/70 shadow-sm">
                                {{ $category->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Product grid --}}
                <div class="mt-6 grid auto-rows-fr grid-cols-1 items-stretch gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                    @forelse ($products as $product)
                        <x-product-card
                            :product="$product"
                            class="h-full" />
                    @empty
                        <p class="dc-card col-span-full text-sm text-brand-text/65">
                            {{ __('No approved products are available from this store yet.') }}
                        </p>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            </section>

            {{-- Store information panels --}}
            <div
                @class([
                    'grid items-start gap-5',
                    'lg:grid-cols-3' => $offers->isNotEmpty(),
                    'lg:grid-cols-2' => $offers->isEmpty(),
                ])>

                {{-- Store offers --}}
                @if ($offers->isNotEmpty())
                    <section class="dc-panel h-full">
                        <p class="text-sm font-semibold text-brand-dark">
                            {{ __('Store offers') }}
                        </p>

                        <h2 class="mt-1 text-lg font-extrabold">
                            {{ __('Current deals') }}
                        </h2>

                        <div class="mt-4 space-y-3">
                            @foreach ($offers as $offer)
                                <article class="rounded-2xl border border-brand-border bg-brand-light p-4">
                                    <h3 class="text-sm font-extrabold text-brand-dark">
                                        {{ $offer->title }}
                                    </h3>

                                    @if ($offer->description)
                                        <p class="mt-2 text-xs leading-5 text-brand-text/65 sm:text-sm">
                                            {{ $offer->description }}
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Store reviews --}}
                <section class="dc-panel h-full">
                    <h2 class="text-lg font-extrabold text-brand-dark">
                        {{ __('Store reviews') }}
                    </h2>

                    <div class="mt-4 space-y-4">
                        @forelse ($reviews as $review)
                            <article class="border-b border-brand-border pb-4 last:border-b-0 last:pb-0">
                                <p class="text-sm font-bold text-brand-dark">
                                    {{ $review->customer?->user?->name }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-brand-orange"
                                    aria-label="{{ __('Rating :rating out of 5', [
                                        'rating' => $review->rating
                                    ]) }}">

                                    {{ str_repeat('★', $review->rating) }}
                                </p>

                                @if ($review->comment)
                                    <p class="mt-2 text-sm leading-6 text-brand-text/65">
                                        {{ $review->comment }}
                                    </p>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-brand-text/65">
                                {{ __('No store reviews yet.') }}
                            </p>
                        @endforelse
                    </div>
                </section>

                {{-- Contact information --}}
                <section class="dc-panel h-full">
                    <h2 class="text-lg font-extrabold text-brand-dark">
                        {{ __('Contact information') }}
                    </h2>

                    <dl class="mt-4 space-y-4 text-sm">

                        {{-- Phone --}}
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-text/50">
                                {{ __('Phone') }}
                            </dt>

                            <dd class="mt-1 break-words font-medium text-brand-text/75">
                                {{
                                    $store->contact_phone
                                    ?: $store->vendor->phone
                                }}
                            </dd>
                        </div>

                        {{-- Email --}}
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-text/50">
                                {{ __('Email') }}
                            </dt>

                            <dd class="mt-1 break-all font-medium text-brand-text/75">
                                {{
                                    $store->contact_email
                                    ?: $store->vendor->user?->email
                                }}
                            </dd>
                        </div>

                        {{-- Address --}}
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-brand-text/50">
                                {{ __('Address') }}
                            </dt>

                            <dd class="mt-1 break-words font-medium leading-6 text-brand-text/75">
                                {{
                                    collect([
                                        $store->vendor->address,
                                        $store->vendor->city,
                                        $store->vendor->district
                                    ])
                                        ->filter()
                                        ->implode(', ')
                                }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </main>

    <x-footer />
</body>

</html>
