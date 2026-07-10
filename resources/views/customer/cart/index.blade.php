<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Cart') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div id="cart-status" class="mb-4 hidden text-sm font-medium"></div>

                <div class="space-y-4">
                    @forelse ($cart->items as $item)
                        @php
                            $lineTotal = (float) $item->unit_price * $item->quantity;
                        @endphp
                        <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 md:flex-row md:items-center md:justify-between">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900">{{ $item->product->name }}</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ __('Line Total') }}:
                                    <span data-cart-line-total="{{ $item->id }}">{{ \App\Services\CurrencyService::formatLkr($lineTotal) }}</span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ $item->variant?->name ?? __('Default') }} · {{ \App\Services\CurrencyService::formatLkr($item->unit_price) }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <form method="POST" action="{{ route('customer.cart.items.update', $item) }}" class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:flex" data-cart-update-form>
                                    @csrf
                                    @method('PATCH')
                                    <x-text-input name="quantity" type="number" min="1" class="w-full sm:w-24" :value="$item->quantity" data-cart-quantity />
                                    <x-secondary-button type="submit" class="whitespace-nowrap">{{ __('Update') }}</x-secondary-button>
                                </form>

                                <form method="POST" action="{{ route('customer.cart.items.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-red-700 underline">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('Your cart is empty.') }}</p>
                    @endforelse
                </div>

                <div class="flex flex-col gap-4 mt-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-lg font-semibold text-gray-900">
                        {{ __('Total') }}:
                        <span id="cart-subtotal">{{ \App\Services\CurrencyService::formatLkr($totals['subtotal']) }}</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @if ($cart->items->isNotEmpty())
                            <form method="POST" action="{{ route('customer.cart.clear') }}">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button type="submit" class="w-full justify-center sm:w-auto">{{ __('Clear Cart') }}</x-secondary-button>
                            </form>

                            <a href="{{ route('customer.checkout.show') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
                status.classList.remove('hidden', 'text-green-700', 'text-red-700');
                status.classList.add(type === 'error' ? 'text-red-700' : 'text-green-700');
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
