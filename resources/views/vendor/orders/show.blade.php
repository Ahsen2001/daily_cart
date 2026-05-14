<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Vendor Order Details') }}</h2>
            <a href="{{ route('vendor.orders.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><div class="text-sm text-gray-500">{{ __('Order') }}</div><div class="font-semibold">{{ $order->order_number }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Customer') }}</div><div class="font-semibold">{{ $order->customer?->user?->name }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Scheduled Delivery') }}</div><div class="font-semibold">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Status') }}</div><div class="font-semibold">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</div></div>
                </div>

                <div class="mt-6 border-t pt-6">
                    <h3 class="mb-3 font-semibold">{{ __('Items') }}</h3>
                    @foreach ($order->items as $item)
                        <div class="flex justify-between border-b py-3 text-sm last:border-b-0">
                            <div>
                                <div class="font-medium">{{ $item->product_name }}</div>
                                <div class="text-gray-500">{{ __('Quantity') }}: {{ $item->quantity }}</div>
                            </div>
                            <div>{{ \App\Services\CurrencyService::formatLkr($item->total_price) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold">{{ __('Order Actions') }}</h3>
                    <div class="space-y-3">
                        @if ($order->order_status === 'pending')
                            <form method="POST" action="{{ route('vendor.orders.confirm', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="w-full justify-center">{{ __('Confirm Order') }}</x-primary-button>
                            </form>
                        @endif
                        @if ($order->order_status === 'confirmed')
                            <form method="POST" action="{{ route('vendor.orders.packed', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="w-full justify-center">{{ __('Mark Packed') }}</x-primary-button>
                            </form>
                        @endif
                        @if (in_array($order->order_status, ['pending', 'confirmed'], true))
                            <form method="POST" action="{{ route('vendor.orders.cancel', $order) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <textarea name="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" required placeholder="{{ __('Cancellation reason') }}"></textarea>
                                <x-danger-button class="w-full justify-center">{{ __('Cancel Order') }}</x-danger-button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold">{{ __('Totals') }}</h3>
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ __('Total') }}</span>
                        <span>{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
