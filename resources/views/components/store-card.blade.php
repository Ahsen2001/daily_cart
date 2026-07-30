@props(['store'])

<article {{ $attributes->merge(['class' => 'group overflow-hidden rounded-3xl border border-brand-border bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-lift']) }}>
    <a href="{{ route('stores.show', $store) }}" class="block">
        <div class="relative h-36 overflow-hidden bg-brand-light">
            <img src="{{ $store->cover_image_url }}" alt="{{ $store->vendor?->store_name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @if ($store->is_featured)
                <span class="absolute right-3 top-3 rounded-full bg-brand-orange px-3 py-1 text-xs font-extrabold text-white">{{ __('Featured') }}</span>
            @endif
        </div>
        <div class="relative p-5 pt-0">
            <img src="{{ $store->logo_url }}" alt="" class="-mt-9 h-16 w-16 rounded-2xl border-4 border-white bg-white object-cover shadow-card">
            <h3 class="mt-3 truncate text-lg font-extrabold text-brand-text">{{ $store->vendor?->store_name }}</h3>
            <p class="mt-1 line-clamp-2 min-h-10 text-sm leading-5 text-brand-text/60">{{ $store->description ?: __('Verified DailyCart vendor storefront.') }}</p>
            <div class="mt-4 flex items-center justify-between gap-3 text-xs font-semibold text-brand-text/60">
                <span>{{ $store->delivery_estimate ?: __('Delivery estimate available') }}</span>
                <span>{{ trans_choice(':count follower|:count followers', $store->followers_count ?? 0, ['count' => $store->followers_count ?? 0]) }}</span>
            </div>
        </div>
    </a>
</article>
