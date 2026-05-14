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

                <div class="space-y-4">
                    @forelse ($cart->items as $item)
                        <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $item->variant?->name ?? __('Default') }} · {{ \App\Services\CurrencyService::formatLkr($item->unit_price) }}
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('customer.cart.items.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <x-text-input name="quantity" type="number" min="1" class="w-24" :value="$item->quantity" />
                                    <x-secondary-button>{{ __('Update') }}</x-secondary-button>
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
                        {{ __('Total') }}: {{ \App\Services\CurrencyService::formatLkr($totals['subtotal']) }}
                    </div>

                    <div class="flex gap-3">
                        @if ($cart->items->isNotEmpty())
                            <form method="POST" action="{{ route('customer.cart.clear') }}">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button>{{ __('Clear Cart') }}</x-secondary-button>
                            </form>

                            <a href="{{ route('customer.checkout.show') }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-gray-800 border border-transparent rounded-md hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ __('Checkout') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
