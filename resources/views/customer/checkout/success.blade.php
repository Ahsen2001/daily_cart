<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Order Placed') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <p class="font-medium text-green-700">{{ __('Your order has been placed successfully.') }}</p>

                <div class="mt-6 space-y-4">
                    @forelse ($orders as $order)
                        <div class="border-b border-gray-100 pb-4">
                            <div class="font-semibold text-gray-900">{{ $order->order_number }}</div>
                            <div class="text-sm text-gray-600">
                                {{ __('Total') }}: {{ \App\Services\CurrencyService::formatLkr($order->total_amount) }} ·
                                {{ __('Status') }}: {{ ucfirst($order->order_status) }} ·
                                {{ __('Payment') }}: {{ ucfirst($order->payment_status) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('No recent order details found.') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    <a href="{{ route('customer.products.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Continue shopping') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
