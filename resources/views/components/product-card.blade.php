@props(['product'])

@php
    $price = $product->discount_price ?: $product->price;
    $rating = method_exists($product, 'averageRating') ? $product->averageRating() : 0;
@endphp

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-3xl border border-green-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-soft']) }}>
    <a href="{{ route('customer.products.show', $product) }}" class="block">
        <div class="aspect-[4/3] bg-brand-light">
            @if ($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full items-center justify-center">
                    <x-application-logo :show-text="false" class="opacity-70" />
                </div>
            @endif
        </div>
        <div class="space-y-3 p-5">
            <div>
                <p class="text-xs font-medium text-brand-dark">{{ $product->category?->name }}</p>
                <h3 class="mt-1 line-clamp-2 text-base font-bold text-brand-text">{{ $product->name }}</h3>
            </div>
            <div class="flex items-center justify-between">
                <p class="font-bold text-brand-dark">{{ \App\Services\CurrencyService::formatLkr($price) }}</p>
                <p class="text-sm text-brand-orange">{{ number_format($rating, 1) }} / 5</p>
            </div>
        </div>
    </a>
    <div class="px-5 pb-5">
        @auth
            @if (Auth::user()->hasPrimaryRole('Customer'))
                <form method="POST" action="{{ route('customer.cart.store', $product) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="dc-button w-full" type="submit">{{ __('Add to Cart') }}</button>
                </form>
            @endif
        @else
            <a class="dc-button w-full" href="{{ route('login') }}">{{ __('Add to Cart') }}</a>
        @endauth
    </div>
</article>
