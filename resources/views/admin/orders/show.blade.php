<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Admin Order Details') }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back') }}</a>
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
                    <div><div class="text-sm text-gray-500">{{ __('Vendor') }}</div><div class="font-semibold">{{ $order->vendor?->store_name }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Scheduled Delivery') }}</div><div class="font-semibold">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Order Status') }}</div><div class="font-semibold">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Delivery Status') }}</div><div class="font-semibold">{{ str_replace('_', ' ', ucfirst($order->delivery?->status ?? 'pending')) }}</div></div>
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
                    <h3 class="mb-3 font-semibold">{{ __('Admin Actions') }}</h3>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="order_status" class="w-full rounded-md border-gray-300 shadow-sm">
                            @foreach (['pending', 'confirmed', 'packed', 'assigned_to_rider', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'] as $status)
                                <option value="{{ $status }}" @selected($order->order_status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                        <x-primary-button class="w-full justify-center">{{ __('Update Status') }}</x-primary-button>
                    </form>

                    @if ($order->order_status === 'packed')
                        <a href="{{ route('admin.orders.assign-rider', $order) }}" class="mt-4 inline-flex w-full justify-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                            {{ __('Assign Rider') }}
                        </a>
                    @endif
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold">{{ __('Payment') }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>{{ __('Method') }}</span><span>{{ str_replace('_', ' ', ucfirst($order->payment?->payment_method ?? 'pending')) }}</span></div>
                        <div class="flex justify-between"><span>{{ __('Status') }}</span><span>{{ str_replace('_', ' ', ucfirst($order->payment?->status ?? $order->payment_status)) }}</span></div>
                        <div class="flex justify-between font-semibold"><span>{{ __('Total') }}</span><span>{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</span></div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 font-semibold text-gray-900 border-b pb-2">{{ __('Tracking Timeline Log') }}</h3>
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
                                        <span class="text-gray-400 text-[10px]"><x-local-time :date="$history->created_at" format="short" /></span>
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
