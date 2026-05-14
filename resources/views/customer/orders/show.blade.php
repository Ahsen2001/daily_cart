<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Order Tracking') }}</h2>
            <a href="{{ route('customer.orders.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back to orders') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Order Number') }}</div>
                            <div class="font-semibold text-gray-900">{{ $order->order_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Scheduled Delivery') }}</div>
                            <div class="font-semibold text-gray-900">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Order Status') }}</div>
                            <div class="font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Delivery Status') }}</div>
                            <div class="font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($order->delivery?->status ?? 'pending')) }}</div>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <h3 class="mb-3 font-semibold text-gray-900">{{ __('Items') }}</h3>
                        <div class="space-y-3">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                                        <div class="text-gray-500">{{ __('Quantity') }}: {{ $item->quantity }}</div>
                                    </div>
                                    <div class="font-medium">{{ \App\Services\CurrencyService::formatLkr($item->total_price) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold text-gray-900">{{ __('Delivery Address') }}</h3>
                    <p class="text-sm text-gray-700">{{ $order->delivery_address }}</p>
                    @if ($order->delivery?->rider)
                        <p class="mt-3 text-sm text-gray-700">{{ __('Rider') }}: {{ $order->delivery->rider->user?->name }}</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold text-gray-900">{{ __('Payment Summary') }}</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($order->subtotal) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($order->discount_amount) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Delivery') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($order->delivery_fee) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Service') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($order->service_charge) }}</dd></div>
                        <div class="flex justify-between border-t pt-2 font-semibold"><dt>{{ __('Total') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</dd></div>
                    </dl>
                </div>

                @if ($order->order_status === 'pending')
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="mb-3 font-semibold text-gray-900">{{ __('Cancel Order') }}</h3>
                        <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <textarea name="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" required placeholder="{{ __('Cancellation reason') }}">{{ old('reason') }}</textarea>
                            <x-danger-button>{{ __('Cancel Order') }}</x-danger-button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
