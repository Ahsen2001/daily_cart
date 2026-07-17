<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $product->name }} - DailyCart</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-brand-light font-sans text-brand-text">
        <header class="sticky top-0 z-40 border-b border-brand-border bg-white/95 backdrop-blur-xl">
            <div class="dc-container flex min-h-20 items-center justify-between gap-4 py-3">
                <a href="{{ route('home') }}" class="transition hover:scale-[1.02]"><x-application-logo /></a>
                <nav class="flex items-center gap-2" aria-label="{{ __('Product navigation') }}">
                    <a class="dc-button-secondary hidden sm:inline-flex" href="{{ route('pages.offers') }}">{{ __('Offers') }}</a>
                    @auth
                        <a class="dc-button" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    @else
                        <a class="dc-button" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="py-10 sm:py-14">
            <div class="dc-container">
                <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-brand-text/60" aria-label="{{ __('Breadcrumb') }}">
                    <a class="hover:text-brand-dark" href="{{ route('products.index') }}">{{ __('Products') }}</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $product->category?->name }}</span>
                    <span aria-hidden="true">/</span>
                    <span class="text-brand-text">{{ $product->name }}</span>
                </nav>

                <section class="grid overflow-hidden rounded-[2rem] border border-brand-border bg-white shadow-soft lg:grid-cols-2" aria-labelledby="product-title">
                    <div class="bg-brand-light p-5 sm:p-8">
                        <div class="aspect-square overflow-hidden rounded-[1.5rem] bg-white">
                            <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        </div>
                        @if ($product->images->isNotEmpty())
                            <div class="mt-4 grid grid-cols-4 gap-3">
                                @foreach ($product->images->take(4) as $image)
                                    <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text ?: $product->name }}" class="aspect-square w-full rounded-2xl border border-brand-border object-cover" loading="lazy">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="p-6 sm:p-10">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-brand-light px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-brand-dark">{{ $product->category?->name }}</span>
                            @if ($pricing['promotion'])
                                <span class="rounded-full bg-orange-100 px-3 py-1.5 text-xs font-extrabold uppercase tracking-wide text-brand-orange">{{ __('Offer active') }}</span>
                            @endif
                        </div>
                        <h1 id="product-title" class="mt-4 text-3xl font-extrabold sm:text-4xl">{{ $product->name }}</h1>
                        <p class="mt-2 text-sm font-semibold text-brand-text/60">{{ $product->vendor?->store_name }}</p>

                        <div class="mt-7 rounded-3xl border border-brand-border bg-brand-light p-5">
                            @if ($pricing['promotion'])
                                <p class="text-sm font-bold text-brand-orange">{{ $pricing['promotion']->title }}</p>
                            @endif
                            <div class="mt-1 flex flex-wrap items-end gap-3">
                                <p id="product-effective-price" class="text-3xl font-extrabold text-brand-dark">{{ \App\Services\CurrencyService::formatLkr($pricing['final_price']) }}</p>
                                <p id="product-base-price" @class(['pb-1 text-sm font-semibold text-brand-text/45 line-through', 'hidden' => $pricing['discount'] <= 0])>{{ \App\Services\CurrencyService::formatLkr($pricing['base_price']) }}</p>
                            </div>
                            <p id="product-saving" @class(['mt-2 text-sm font-bold text-emerald-700', 'hidden' => $pricing['discount'] <= 0])>{{ __('You save :amount', ['amount' => \App\Services\CurrencyService::formatLkr($pricing['discount'])]) }}</p>
                            @if ($pricing['promotion'])
                                <p class="mt-2 text-xs text-brand-text/55">{{ __('Offer valid until :date', ['date' => $pricing['promotion']->ends_at->format('M d, Y - g:i A')]) }}</p>
                            @endif
                        </div>

                        @if ($product->description)
                            <p class="mt-6 text-sm leading-7 text-brand-text/70">{{ $product->description }}</p>
                        @endif

                        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                            <div class="rounded-2xl border border-brand-border p-4"><dt class="font-semibold text-brand-text/55">{{ __('Brand') }}</dt><dd class="mt-1 font-bold">{{ $product->brand ?: '-' }}</dd></div>
                            <div class="rounded-2xl border border-brand-border p-4"><dt class="font-semibold text-brand-text/55">{{ __('Stock') }}</dt><dd class="mt-1 font-bold">{{ $product->stock_quantity }}</dd></div>
                        </dl>

                        @auth
                            @if (Auth::user()->hasPrimaryRole('Customer'))
                                <form method="POST" action="{{ route('customer.cart.store', $product) }}" class="mt-7 space-y-4">
                                    @csrf
                                    @if ($product->variants->isNotEmpty())
                                        <div>
                                            <x-input-label for="product_variant_id" :value="__('Variant')" />
                                            <select id="product_variant_id" name="product_variant_id" class="mt-1 block w-full rounded-2xl border-brand-border shadow-sm focus:border-brand-primary focus:ring-brand-primary">
                                                <option value="" data-price="{{ $pricing['final_price'] }}" data-base-price="{{ $pricing['base_price'] }}" data-discount="{{ $pricing['discount'] }}">{{ __('Default') }} - {{ \App\Services\CurrencyService::formatLkr($pricing['final_price']) }}</option>
                                                @foreach ($product->variants as $variant)
                                                    @php($variantPrice = $variantPricing[$variant->id])
                                                    <option value="{{ $variant->id }}" data-price="{{ $variantPrice['final_price'] }}" data-base-price="{{ $variantPrice['base_price'] }}" data-discount="{{ $variantPrice['discount'] }}">{{ $variant->name }} - {{ \App\Services\CurrencyService::formatLkr($variantPrice['final_price']) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div>
                                        <x-input-label for="quantity" :value="__('Quantity')" />
                                        <x-text-input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock_quantity }}" value="1" class="mt-1 block w-32" />
                                    </div>
                                    <button class="dc-button w-full sm:w-auto" type="submit">{{ __('Add offer to cart') }}</button>
                                </form>
                            @else
                                <a class="dc-button mt-7" href="{{ route('dashboard') }}">{{ __('Return to dashboard') }}</a>
                            @endif
                        @else
                            <div class="mt-7 rounded-3xl border border-brand-border bg-white p-5">
                                <p class="text-sm text-brand-text/65">{{ __('Sign in or create a customer account to add this offer to your cart.') }}</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a class="dc-button" href="{{ route('login') }}">{{ __('Login to buy') }}</a>
                                    <a class="dc-button-secondary" href="{{ route('register') }}">{{ __('Create account') }}</a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </section>
            </div>
        </main>

        <x-footer />

        <script>
            const variantSelect = document.getElementById('product_variant_id');

            variantSelect?.addEventListener('change', () => {
                const option = variantSelect.selectedOptions[0];
                const price = Number(option.dataset.price || 0);
                const basePrice = Number(option.dataset.basePrice || 0);
                const discount = Number(option.dataset.discount || 0);
                const formatter = new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR' });
                const effectivePrice = document.getElementById('product-effective-price');
                const originalPrice = document.getElementById('product-base-price');
                const saving = document.getElementById('product-saving');

                effectivePrice.textContent = formatter.format(price);
                originalPrice.textContent = formatter.format(basePrice);
                saving.textContent = `{{ __('You save') }} ${formatter.format(discount)}`;
                originalPrice.classList.toggle('hidden', discount <= 0);
                saving.classList.toggle('hidden', discount <= 0);
            });
        </script>
    </body>
</html>
