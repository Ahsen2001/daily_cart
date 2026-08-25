@props([
'promotions' => collect(),
'contained' => false,
])

@if ($promotions->isNotEmpty())
<section
    {{ $attributes->class([
            'overflow-hidden border-y border-brand-border bg-gradient-to-br from-brand-dark via-brand-primary to-emerald-600 py-8 text-white sm:py-10',
            'rounded-3xl border' => $contained,
        ]) }}
    aria-labelledby="offers-today-title">

    <div
        @class([ 'dc-container'=> ! $contained,
        'px-4 sm:px-6' => $contained,
        ])>

        {{-- Section header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">

                {{-- Live savings badge --}}
                <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.15em] ring-1 ring-white/20">
                    <span
                        class="h-2 w-2 shrink-0 animate-blink rounded-full bg-amber-300"
                        aria-hidden="true">
                    </span>

                    {{ __('Live savings') }}
                </p>

                {{-- Heading --}}
                <h2
                    id="offers-today-title"
                    class="mt-3 text-xl font-extrabold tracking-tight sm:text-2xl">

                    {{ __('Offers Today') }}
                </h2>

                {{-- Description --}}
                <p class="mt-2 max-w-xl text-xs leading-6 text-white/80 sm:text-sm">
                    {{ __('Fresh, active offers announced by DailyCart and approved vendors.') }}
                </p>
            </div>

            {{-- View all offers --}}
            <a
                class="inline-flex min-h-10 w-fit items-center justify-center gap-2 self-start rounded-full bg-white px-5 py-2.5 text-xs font-bold text-brand-dark shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-lift lg:shrink-0 lg:self-auto"
                href="{{ route('pages.offers') }}">

                {{ __('View all offers') }}

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                    aria-hidden="true">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 12h14m-5-5 5 5-5 5">
                    </path>
                </svg>
            </a>
        </div>

        {{-- Offers grid --}}
        <div class="mt-6 grid grid-cols-1 justify-items-center gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($promotions as $promotion)
            @php
            $offerProduct = $promotion->target_type === 'product'
            ? $promotion->targetProduct
            : null;

            $offerImage = $promotion->banner_image
            ? \Illuminate\Support\Facades\Storage::url(
            $promotion->banner_image
            )
            : $offerProduct?->display_image_url;

            $offerUrl = $offerProduct
            ? route('products.show', [
            'product' => $offerProduct->slug,
            'promotion' => $promotion->id,
            ])
            : route('pages.offers');

            $discountLabel = $promotion->discount_type === 'percentage'
            ? rtrim(
            rtrim(
            number_format(
            (float) $promotion->discount_value,
            2
            ),
            '0'
            ),
            '.'
            ).'% OFF'
            : \App\Services\CurrencyService::formatLkr(
            $promotion->discount_value
            ).' OFF';

            $offerBasePrice = $offerProduct
            ? (float) (
            $offerProduct->discount_price
            ?: $offerProduct->price
            )
            : null;

            $offerPrice = $offerBasePrice !== null
            ? $promotion->priceFor($offerBasePrice)
            : null;
            @endphp

            {{-- Offer card --}}
            <article class="group flex h-full w-full max-w-sm flex-col overflow-hidden rounded-2xl bg-white text-brand-text shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                <a
                    href="{{ $offerUrl }}"
                    class="flex h-full flex-col"
                    aria-label="{{ __('View :offer', [
                                'offer' => $promotion->title
                            ]) }}">

                    {{-- Image --}}
                    <div class="relative h-44 w-full shrink-0 overflow-hidden bg-brand-light sm:h-48">
                        @if ($offerImage)
                        <img
                            src="{{ $offerImage }}"
                            alt="{{ $promotion->title }}"
                            class="block h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                            decoding="async">
                        @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-amber-50">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt=""
                                class="h-16 w-16 object-contain opacity-80">
                        </div>
                        @endif

                        {{-- Discount badge --}}
                        <span class="absolute left-2.5 top-2.5 rounded-full bg-brand-orange px-3 py-1.5 text-[10px] font-extrabold text-white shadow-card sm:text-xs">
                            {{ $discountLabel }}
                        </span>

                        {{-- Promotion type --}}
                        <span class="absolute right-2.5 top-2.5 max-w-[45%] truncate rounded-full bg-white/90 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-wide text-brand-dark backdrop-blur sm:text-[10px]">
                            {{ str_replace(
                                        '_',
                                        ' ',
                                        $promotion->promotion_type
                                    ) }}
                        </span>
                    </div>

                    {{-- Card information --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-[10px] font-bold uppercase tracking-wide text-brand-dark sm:text-xs">
                                    {{
                                                $offerProduct?->category?->name
                                                ?? $promotion->vendor?->store_name
                                                ?? __('DailyCart special')
                                            }}
                                </p>

                                <h3 class="mt-1.5 line-clamp-2 text-sm font-extrabold leading-5 sm:text-base">
                                    {{ $promotion->title }}
                                </h3>
                            </div>

                            {{-- Arrow --}}
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-light text-brand-dark transition group-hover:bg-brand-primary group-hover:text-white"
                                aria-hidden="true">

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h14m-5-5 5 5-5 5">
                                    </path>
                                </svg>
                            </span>
                        </div>

                        @if ($offerProduct)
                        {{-- Product and pricing --}}
                        <div class="mt-3 flex items-end justify-between gap-3 border-t border-brand-border pt-3">
                            <p class="line-clamp-1 min-w-0 text-xs font-semibold text-brand-text/70 sm:text-sm">
                                {{ $offerProduct->name }}
                            </p>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-extrabold text-brand-dark">
                                    {{ \App\Services\CurrencyService::formatLkr(
                                                    $offerPrice
                                                ) }}
                                </p>

                                <p class="text-[10px] text-brand-text/45 line-through sm:text-xs">
                                    {{ \App\Services\CurrencyService::formatLkr(
                                                    $offerBasePrice
                                                ) }}
                                </p>
                            </div>
                        </div>
                        @elseif ($promotion->description)
                        <p class="mt-3 line-clamp-2 text-xs leading-5 text-brand-text/65 sm:text-sm">
                            {{ $promotion->description }}
                        </p>
                        @endif

                        {{-- Expiry --}}
                        <p class="mt-auto pt-3 text-[10px] font-semibold text-brand-text/55 sm:text-xs">
                            {{ __('Ends :date', [
                                        'date' => $promotion->ends_at->format(
                                            'M d, Y - g:i A'
                                        )
                                    ]) }}
                        </p>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif