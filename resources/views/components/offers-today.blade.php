@props([
    'promotions' => collect(),
    'contained' => false,
])

@if ($promotions->isNotEmpty())
    <section {{ $attributes->class(['overflow-hidden border-y border-brand-border bg-gradient-to-br from-brand-dark via-brand-primary to-emerald-600 py-14 text-white', 'rounded-[2rem] border' => $contained]) }} aria-labelledby="offers-today-title">
        <div @class(['dc-container' => ! $contained, 'px-5 sm:px-8' => $contained])>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] ring-1 ring-white/20">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-amber-300" aria-hidden="true"></span>
                        {{ __('Live savings') }}
                    </p>
                    <h2 id="offers-today-title" class="mt-4 text-3xl font-extrabold sm:text-4xl">{{ __('Offers Today') }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">{{ __('Fresh, active offers announced by DailyCart and approved vendors.') }}</p>
                </div>
                <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-brand-dark shadow-card transition hover:-translate-y-0.5 hover:shadow-lift" href="{{ route('pages.offers') }}">
                    {{ __('View all offers') }}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                </a>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($promotions as $promotion)
                    @php
                        $offerProduct = $promotion->target_type === 'product' ? $promotion->targetProduct : null;
                        $offerImage = $promotion->banner_image
                            ? \Illuminate\Support\Facades\Storage::url($promotion->banner_image)
                            : $offerProduct?->display_image_url;
                        $offerUrl = $offerProduct ? route('products.show', $offerProduct) : route('pages.offers');
                        $discountLabel = $promotion->discount_type === 'percentage'
                            ? rtrim(rtrim(number_format((float) $promotion->discount_value, 2), '0'), '.').'% OFF'
                            : \App\Services\CurrencyService::formatLkr($promotion->discount_value).' OFF';
                        $offerBasePrice = $offerProduct ? (float) ($offerProduct->discount_price ?: $offerProduct->price) : null;
                        $offerPrice = $offerBasePrice !== null ? $promotion->priceFor($offerBasePrice) : null;
                    @endphp

                    <article class="group overflow-hidden rounded-[1.75rem] bg-white text-brand-text shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                        <a href="{{ $offerUrl }}" class="block h-full" aria-label="{{ __('View :offer', ['offer' => $promotion->title]) }}">
                            <div class="relative aspect-[16/9] overflow-hidden bg-brand-light">
                                @if ($offerImage)
                                    <img src="{{ $offerImage }}" alt="{{ $promotion->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                                @else
                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-emerald-50 to-amber-50">
                                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-28 w-28 object-contain opacity-80">
                                    </div>
                                @endif
                                <span class="absolute left-4 top-4 rounded-full bg-brand-orange px-4 py-2 text-sm font-extrabold text-white shadow-card">{{ $discountLabel }}</span>
                                <span class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-2 text-xs font-bold uppercase tracking-wide text-brand-dark backdrop-blur">{{ str_replace('_', ' ', $promotion->promotion_type) }}</span>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-brand-dark">{{ $offerProduct?->category?->name ?? $promotion->vendor?->store_name ?? __('DailyCart special') }}</p>
                                        <h3 class="mt-2 line-clamp-2 text-xl font-extrabold">{{ $promotion->title }}</h3>
                                    </div>
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-light text-brand-dark transition group-hover:bg-brand-primary group-hover:text-white" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                                    </span>
                                </div>
                                @if ($offerProduct)
                                    <div class="mt-4 flex items-end justify-between gap-3 border-t border-brand-border pt-4">
                                        <p class="line-clamp-1 text-sm font-semibold text-brand-text/70">{{ $offerProduct->name }}</p>
                                        <div class="shrink-0 text-right">
                                            <p class="font-extrabold text-brand-dark">{{ \App\Services\CurrencyService::formatLkr($offerPrice) }}</p>
                                            <p class="text-xs text-brand-text/45 line-through">{{ \App\Services\CurrencyService::formatLkr($offerBasePrice) }}</p>
                                        </div>
                                    </div>
                                @elseif ($promotion->description)
                                    <p class="mt-4 line-clamp-2 text-sm leading-6 text-brand-text/65">{{ $promotion->description }}</p>
                                @endif
                                <p class="mt-4 text-xs font-semibold text-brand-text/55">{{ __('Ends :date', ['date' => $promotion->ends_at->format('M d, Y - g:i A')]) }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
