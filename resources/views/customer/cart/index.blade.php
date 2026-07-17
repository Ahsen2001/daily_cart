<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Your basket') }}</p><h2 class="dc-page-title">{{ __('Shopping Cart') }}</h2></div>
            <a href="{{ route('customer.products.index') }}" class="dc-button-secondary">{{ __('Continue shopping') }}</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            <div class="dc-panel">
                @if (session('status'))
                    <div class="dc-flash dc-flash-success mb-5" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="dc-flash dc-flash-error mb-5" role="alert">{{ $errors->first() }}</div>
                @endif

                <div id="cart-status" class="mb-4 hidden rounded-2xl border px-4 py-3 text-sm font-semibold" role="status" aria-live="polite"></div>

                <div class="space-y-4">
                    @forelse ($cart->items as $item)
                        @php
                            $lineTotal = (float) $item->unit_price * $item->quantity;
                        @endphp
                        <div class="flex flex-col gap-4 rounded-2xl border border-brand-border p-4 transition hover:border-brand-primary/30 md:flex-row md:items-center md:justify-between">
                            <div class="flex min-w-0 items-center gap-4">
                                <img src="{{ $item->product->display_image_url }}" alt="" loading="lazy" class="h-20 w-20 shrink-0 rounded-2xl bg-brand-light object-cover">
                                <div class="min-w-0">
                                <div class="font-semibold text-gray-900">{{ $item->product->name }}</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ __('Line Total') }}:
                                    <span data-cart-line-total="{{ $item->id }}">{{ \App\Services\CurrencyService::formatLkr($lineTotal) }}</span>
                                </div>
                                <div class="text-sm text-brand-muted">
                                    {{ $item->variant?->name ?? __('Default') }} <span aria-hidden="true">&middot;</span> {{ \App\Services\CurrencyService::formatLkr($item->unit_price) }}
                                </div>
                                <div class="hidden">
                                    {{ $item->variant?->name ?? __('Default') }} · {{ \App\Services\CurrencyService::formatLkr($item->unit_price) }}
                                </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <form method="POST" action="{{ route('customer.cart.items.update', $item) }}" class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:flex" data-cart-update-form>
                                    @csrf
                                    @method('PATCH')
                                    <x-text-input name="quantity" type="number" min="1" class="w-full sm:w-24" :value="$item->quantity" data-cart-quantity />
                                    <x-secondary-button type="submit" class="whitespace-nowrap">{{ __('Update') }}</x-secondary-button>
                                </form>

                                <form method="POST" action="{{ route('customer.cart.items.destroy', $item) }}" data-confirm="{{ __('Remove this item from your cart?') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex min-h-11 items-center px-3 text-sm font-bold text-red-700 hover:text-red-900">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="{{ __('Your cart is empty') }}" message="{{ __('Browse the marketplace and add your everyday essentials.') }}" :action="route('customer.products.index')" action-label="{{ __('Start shopping') }}" />
                    @endforelse
                </div>

                <div class="mt-6 flex flex-col gap-4 rounded-2xl bg-brand-light p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-lg font-bold text-gray-900">
                        {{ __('Total') }}:
                        <span id="cart-subtotal">{{ \App\Services\CurrencyService::formatLkr($totals['subtotal']) }}</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @if ($cart->items->isNotEmpty())
                            <form method="POST" action="{{ route('customer.cart.clear') }}" data-confirm="{{ __('Clear every item from your cart?') }}">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button type="submit" class="w-full justify-center sm:w-auto">{{ __('Clear Cart') }}</x-secondary-button>
                            </form>

                            <a href="{{ route('customer.checkout.show') }}" class="dc-button">
                                {{ __('Checkout') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const status = document.getElementById('cart-status');
            const subtotal = document.getElementById('cart-subtotal');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const timers = new WeakMap();

            const showStatus = (message, type = 'success') => {
                if (!status) return;

                status.textContent = message;
                status.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-900', 'border-red-200', 'bg-red-50', 'text-red-900');
                status.classList.add(...(type === 'error'
                    ? ['border-red-200', 'bg-red-50', 'text-red-900']
                    : ['border-green-200', 'bg-green-50', 'text-green-900']));
            };

            const updateCartItem = async (form) => {
                const input = form.querySelector('[data-cart-quantity]');
                const quantity = Number.parseInt(input.value, 10);

                if (!Number.isInteger(quantity) || quantity < 1) {
                    showStatus('{{ __('Quantity must be at least 1.') }}', 'error');
                    return;
                }

                const formData = new FormData(form);

                input.disabled = true;
                showStatus('{{ __('Updating cart...') }}');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || '{{ __('Could not update cart.') }}');
                    }

                    const lineTotal = document.querySelector(`[data-cart-line-total="${data.item.id}"]`);
                    if (lineTotal) {
                        lineTotal.textContent = data.item.formatted_line_total;
                    }

                    if (subtotal) {
                        subtotal.textContent = data.totals.formatted_subtotal;
                    }

                    showStatus(data.message || '{{ __('Cart updated.') }}');
                } catch (error) {
                    showStatus(error.message || '{{ __('Could not update cart.') }}', 'error');
                } finally {
                    input.disabled = false;
                }
            };

            document.querySelectorAll('[data-cart-update-form]').forEach((form) => {
                const input = form.querySelector('[data-cart-quantity]');

                input.addEventListener('input', () => {
                    clearTimeout(timers.get(input));
                    timers.set(input, setTimeout(() => updateCartItem(form), 500));
                });

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    clearTimeout(timers.get(input));
                    updateCartItem(form);
                });
            });
        });
    </script>
</x-app-layout>
