<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $store->vendor->store_name }} - DailyCart</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-light font-sans text-brand-text">
    <header class="sticky top-0 z-40 border-b border-brand-border bg-white/95 backdrop-blur-xl">
        <div class="dc-container flex h-20 items-center justify-between">
            <a href="{{ route('home') }}"><x-application-logo /></a>
            <a class="dc-button-secondary" href="{{ route('stores.index') }}">{{ __('All Stores') }}</a>
        </div>
    </header>

    <main class="pb-14">
        <section class="relative overflow-hidden bg-brand-dark">
            <img src="{{ $store->cover_image_url }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30">
            <div class="dc-container relative py-12 sm:py-16">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                    <img src="{{ $store->logo_url }}" alt="{{ $store->vendor->store_name }}" class="h-28 w-28 rounded-3xl border-4 border-white bg-white object-cover shadow-lift">
                    <div class="text-white">
                        <p class="text-sm font-bold uppercase tracking-[.18em] text-white/70">{{ __('DailyCart store') }}</p>
                        <h1 class="mt-2 text-3xl font-extrabold sm:text-5xl">{{ $store->vendor->store_name }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/85">{{ $store->description ?: __('An approved DailyCart vendor storefront.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="dc-container relative -mt-6 space-y-8">
            <section class="rounded-3xl border border-brand-border bg-white p-5 shadow-card">
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-6 xl:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase text-brand-text/50">{{ __('Rating') }}</p>
                        <p class="mt-1 text-lg font-extrabold">{{ $rating ? number_format($rating, 1) : __('New') }} <span class="text-brand-orange">&#9733;</span></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-brand-text/50">{{ __('Delivery') }}</p>
                        <p class="mt-1 font-bold">{{ $store->delivery_estimate ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-brand-text/50">{{ __('Minimum order') }}</p>
                        <p class="mt-1 font-bold">{{ $store->minimum_order ? AppServicesCurrencyService::formatLkr($store->minimum_order) : __('No minimum') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-brand-text/50">{{ __('Opening hours') }}</p>
                        <p class="mt-1 font-bold">{{ data_get($store->opening_hours, 'summary', __('Contact store')) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-brand-text/50">{{ __('Followers') }}</p>
                        <p class="mt-1 font-bold">{{ number_format($store->followers_count) }}</p>
                    </div>
                    @auth
                        @if (Auth::user()->hasPrimaryRole('Customer'))
                            <div class="flex sm:justify-start xl:justify-end">
                                <form method="POST" action="{{ $isFollowing ? route('customer.stores.unfollow', $store) : route('customer.stores.follow', $store) }}">
                                    @csrf
                                    @if ($isFollowing)
                                        @method('DELETE')
                                    @endif
                                    <button class="{{ $isFollowing ? 'dc-button-secondary' : 'dc-button' }} whitespace-nowrap" type="submit">
                                        {{ $isFollowing ? __('Following') : __('Follow store') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </section>

            @if ($store->banners->isNotEmpty())
                <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($store->banners as $banner)
                        <a href="{{ $banner->link_url ?: '#' }}" class="overflow-hidden rounded-3xl bg-white shadow-card">
                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="aspect-[16/6] w-full object-cover">
                            <p class="p-4 font-bold">{{ $banner->title }}</p>
                        </a>
                    @endforeach
                </section>
            @endif

            <section>
                <div>
                    <p class="font-semibold text-brand-dark">{{ __('Store products') }}</p>
                    <h2 class="mt-1 text-2xl font-extrabold">{{ __('Shop from :store', ['store' => $store->vendor->store_name]) }}</h2>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                        <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-brand-text/70">{{ $category->name }}</span>
                    @endforeach
                </div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse ($products as $product)
                        <x-product-card :product="$product" />
                    @empty
                        <p class="dc-card col-span-full">{{ __('No approved products are available from this store yet.') }}</p>
                    @endforelse
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            </section>

            <div class="grid gap-6 {{ $offers->isNotEmpty() ? 'xl:grid-cols-3' : 'lg:grid-cols-2' }}">
                @if ($offers->isNotEmpty())
                    <section class="dc-panel h-full">
                        <p class="font-semibold text-brand-dark">{{ __('Store offers') }}</p>
                        <h2 class="mt-1 text-xl font-extrabold">{{ __('Current deals') }}</h2>
                        <div class="mt-5 space-y-3">
                            @foreach ($offers as $offer)
                                <article class="rounded-2xl border border-brand-border bg-brand-light p-4">
                                    <h3 class="font-extrabold">{{ $offer->title }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-brand-text/65">{{ $offer->description }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="dc-panel h-full">
                    <h2 class="text-xl font-extrabold">{{ __('Store reviews') }}</h2>
                    <div class="mt-4 space-y-4">
                        @forelse ($reviews as $review)
                            <article class="border-b border-brand-border pb-4">
                                <p class="font-bold">{{ $review->customer?->user?->name }}</p>
                                <p class="mt-1 text-brand-orange">{{ str_repeat('★', $review->rating) }}</p>
                                <p class="mt-2 text-sm text-brand-text/65">{{ $review->comment }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-brand-text/65">{{ __('No store reviews yet.') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="dc-panel h-full">
                    <h2 class="text-xl font-extrabold">{{ __('Contact information') }}</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="font-semibold text-brand-text/55">{{ __('Phone') }}</dt>
                            <dd>{{ $store->contact_phone ?: $store->vendor->phone }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-brand-text/55">{{ __('Email') }}</dt>
                            <dd>{{ $store->contact_email ?: $store->vendor->user?->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-brand-text/55">{{ __('Address') }}</dt>
                            <dd>{{ collect([$store->vendor->address, $store->vendor->city, $store->vendor->district])->filter()->implode(', ') }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </main>

    <x-footer />
</body>
</html>