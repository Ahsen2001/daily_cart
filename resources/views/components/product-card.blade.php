@props([
'product',
'bulkSelectable' => false,
])

@php
$price = $product->discount_price ?: $product->price;

$rating = $product->visible_reviews_avg_rating
?? (
method_exists($product, 'averageRating')
? $product->averageRating()
: 0
);
@endphp

<article
    {{ $attributes->merge([
        'class' => 'group flex h-full flex-col overflow-hidden rounded-3xl border border-brand-border bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:border-brand-primary/30 hover:shadow-lift'
    ]) }}>

    {{-- Product image --}}
    <a
        href="{{ route('products.show', [
            'product' => $product->slug
        ]) }}"
        class="block shrink-0 overflow-hidden">

        <div class="aspect-[4/3] overflow-hidden bg-brand-light">
            <img
                src="{{ $product->display_image_url }}"
                alt="{{ $product->name }}"
                loading="lazy"
                decoding="async"
                class="block h-full w-full object-cover transition duration-500 group-hover:scale-105">
        </div>
    </a>

    {{-- Product information --}}
    <div class="flex flex-1 flex-col p-5">

        {{-- Category and product name --}}
        <div>
            <p class="text-xs font-medium text-brand-dark">
                {{ $product->category?->name }}
            </p>

            <a
                href="{{ route('products.show', [
                    'product' => $product->slug
                ]) }}"
                class="block">

                <h3 class="mt-1 line-clamp-2 text-base font-bold leading-6 text-brand-text transition hover:text-brand-primary">
                    {{ $product->name }}
                </h3>
            </a>
        </div>

        {{-- Price and rating --}}
        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="font-bold text-brand-dark">
                {{ \App\Services\CurrencyService::formatLkr($price) }}
            </p>

            <p
                class="inline-flex shrink-0 items-center gap-1 text-sm font-bold text-brand-orange"
                aria-label="{{ __('Rating :rating out of 5', [
                    'rating' => number_format($rating, 1)
                ]) }}">

                <span aria-hidden="true">★</span>

                {{ number_format($rating, 1) }}
            </p>
        </div>

        {{-- Vendor --}}
        @if ($product->vendor?->storeProfile)
        <div class="mt-4">
            <p class="text-xs text-brand-text/60">
                {{ __('Sold by:') }}
            </p>

            <a
                href="{{ route(
                        'stores.show',
                        $product->vendor->storeProfile
                    ) }}"
                class="mt-1 line-clamp-2 text-xs font-bold leading-5 text-brand-dark transition hover:text-brand-primary hover:underline">

                {{ $product->vendor->store_name }}
            </a>
        </div>
        @endif
    </div>

    {{-- Product actions --}}
    <div class="mt-auto px-5 pb-5">
        @if ($bulkSelectable)
        <div class="mb-3 flex items-center justify-between gap-3 rounded-2xl bg-brand-light px-3 py-2">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-brand-text">
                <input
                    type="hidden"
                    form="bulk-cart-form"
                    name="items[{{ $product->id }}][product_id]"
                    value="{{ $product->id }}">

                <input
                    type="checkbox"
                    form="bulk-cart-form"
                    name="items[{{ $product->id }}][selected]"
                    value="1"
                    data-bulk-product
                    class="rounded border-brand-border text-brand-primary focus:ring-brand-primary">

                {{ __('Select') }}
            </label>

            <label class="flex items-center gap-2 text-xs font-semibold text-brand-text/70">
                {{ __('Qty') }}

                <input
                    type="number"
                    form="bulk-cart-form"
                    name="items[{{ $product->id }}][quantity]"
                    value="1"
                    min="1"
                    max="999"
                    class="w-16 rounded-lg border-brand-border py-1 text-center text-sm"
                    aria-label="{{ __('Quantity for :product', [
                            'product' => $product->name
                        ]) }}">
            </label>
        </div>
        @endif

        @auth
        @if (Auth::user()->hasPrimaryRole('Customer'))
        <form
            method="POST"
            action="{{ route(
                        'customer.cart.store',
                        $product
                    ) }}">

            @csrf

            <input
                type="hidden"
                name="quantity"
                value="1">

            <button
                class="dc-button w-full"
                type="submit">

                {{ __('Add to Cart') }}
            </button>
        </form>
        @endif
        @else
        <a
            class="dc-button w-full"
            href="{{ route('login') }}">

            {{ __('Add to Cart') }}
        </a>
        @endauth
    </div>

</article>
