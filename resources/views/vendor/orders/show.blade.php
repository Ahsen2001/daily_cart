@php
    use App\Services\CurrencyService;

    $vendorItemsTotal = (float) $order->items->sum('total_price');
    $vendorNetTotal = max((float) $order->subtotal - (float) $order->discount_amount - (float) $order->loyalty_discount_amount, 0);
@endphp

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
                    <h3 class="mb-3 font-semibold">{{ __('Vendor Total') }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>{{ __('Items subtotal') }}</span>
                            <span>{{ CurrencyService::formatLkr($vendorItemsTotal) }}</span>
                        </div>
                        @if ((float) $order->discount_amount > 0)
                            <div class="flex justify-between text-green-700">
                                <span>{{ __('Order discount') }}</span>
                                <span>-{{ CurrencyService::formatLkr($order->discount_amount) }}</span>
                            </div>
                        @endif
                        @if ((float) $order->loyalty_discount_amount > 0)
                            <div class="flex justify-between text-green-700">
                                <span>{{ __('Loyalty discount') }}</span>
                                <span>-{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-100 pt-3 font-semibold">
                            <span>{{ __('Vendor payable total') }}</span>
                            <span>{{ CurrencyService::formatLkr($vendorNetTotal) }}</span>
                        </div>
                        <p class="text-xs text-gray-500">{{ __('Delivery and service fees are excluded from the vendor total.') }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 font-semibold text-gray-900 border-b pb-2">{{ __('Order Tracking History') }}</h3>
                    <div class="space-y-4">
                        @forelse ($order->statusHistories()->latest()->get() as $history)
                            <div class="flex gap-3 text-xs">
                                <div class="flex flex-col items-center">
                                    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        ✓
                                    </div>
                                    @if (! $loop->last)
                                        <div class="h-full w-0.5 bg-gray-100 mt-1 min-h-[15px]"></div>
                                    @endif
                                </div>
                                <div class="flex-1 pb-2">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-800">{{ str_replace('_', ' ', ucfirst($history->status)) }}</span>
                                        <span class="text-gray-400 text-[10px]">{{ $history->created_at->format('M d H:i') }}</span>
                                    </div>
                                    @if ($history->remarks)
                                        <p class="text-gray-500 mt-0.5 italic">{{ $history->remarks }}</p>
                                    @endif
                                    @if ($history->updater)
                                        <p class="text-[9px] text-gray-400 mt-0.5">{{ __('By') }}: {{ $history->updater->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 italic">{{ __('No tracking status updates logged yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
