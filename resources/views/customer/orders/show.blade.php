@php
    use App\Services\CurrencyService;

    $statusSteps = [
        'pending' => __('Order placed'),
        'confirmed' => __('Confirmed'),
        'packed' => __('Packed'),
        'assigned_to_rider' => __('Rider assigned'),
        'out_for_delivery' => __('Out for delivery'),
        'delivered' => __('Delivered'),
    ];
    $statusKeys = array_keys($statusSteps);
    $currentIndex = max(0, array_search($order->order_status, $statusKeys, true) ?: 0);
    $isTerminal = in_array($order->order_status, ['cancelled', 'refunded'], true);
    $progressPercent = $isTerminal ? 100 : (int) round(($currentIndex + 1) / count($statusSteps) * 100);
    $latestLocation = $order->delivery?->rider?->locations?->sortByDesc('recorded_at')->first();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Order Tracking') }}</h2>
                <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($order->order_status === 'delivered')
                    <a href="{{ route('customer.orders.receipt', $order) }}" class="rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">{{ __('Print Receipt') }}</a>
                @endif
                <a href="{{ route('customer.orders.index') }}" class="text-sm font-medium text-green-700 underline">{{ __('Back to orders') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl bg-white p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <section class="rounded-3xl bg-green-700 p-6 text-white shadow-sm sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-green-100">{{ __('Live order status') }}</p>
                        <h1 class="mt-2 text-3xl font-bold">{{ $isTerminal ? str_replace('_', ' ', ucfirst($order->order_status)) : $statusSteps[$order->order_status] ?? str_replace('_', ' ', ucfirst($order->order_status)) }}</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-green-100">
                            {{ __('Scheduled delivery') }}: {{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 text-left lg:text-right">
                        <p class="text-xs uppercase tracking-wide text-green-100">{{ __('Delivery progress') }}</p>
                        <p class="mt-1 text-2xl font-bold">{{ $progressPercent }}%</p>
                        <p class="text-xs text-green-100">{{ __('Payment') }}: {{ str_replace('_', ' ', ucfirst($order->payment_status)) }}</p>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <section class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ __('Delivery Timeline') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('Secure status updates for your DailyCart order.') }}</p>
                            </div>
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">{{ $order->delivery?->status ? str_replace('_', ' ', ucfirst($order->delivery->status)) : __('Pending') }}</span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-green-600 transition-all" style="width: {{ $progressPercent }}%"></div>
                        </div>

                        <div class="mt-6 space-y-5">
                            @foreach ($statusSteps as $key => $label)
                                @php
                                    $stepIndex = array_search($key, $statusKeys, true);
                                    $isDone = ! $isTerminal && $stepIndex < $currentIndex;
                                    $isActive = ! $isTerminal && $stepIndex === $currentIndex;
                                @endphp
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 {{ $isDone ? 'border-green-600 bg-green-600 text-white' : ($isActive ? 'border-green-600 bg-white text-green-700' : 'border-gray-200 bg-gray-50 text-gray-400') }} text-sm font-bold">
                                            {{ $isDone ? '✓' : $stepIndex + 1 }}
                                        </div>
                                        @if (! $loop->last)
                                            <div class="mt-2 h-full min-h-8 w-0.5 {{ $isDone ? 'bg-green-200' : 'bg-gray-100' }}"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-2">
                                        <p class="font-semibold {{ $isActive ? 'text-green-700' : 'text-gray-900' }}">{{ $label }}</p>
                                        <p class="text-sm text-gray-500">
                                            @switch($key)
                                                @case('pending')
                                                    {{ $order->placed_at?->format('M d, Y h:i A') ?? __('Waiting for vendor confirmation.') }}
                                                    @break
                                                @case('assigned_to_rider')
                                                    {{ $order->delivery?->rider?->user?->name ? __('Assigned to :rider', ['rider' => $order->delivery->rider->user->name]) : __('Waiting for rider assignment.') }}
                                                    @break
                                                @case('delivered')
                                                    {{ $order->delivery?->delivered_at?->format('M d, Y h:i A') ?? __('Delivery proof will appear after completion.') }}
                                                    @break
                                                @default
                                                    {{ $isActive ? __('In progress now.') : __('Pending update.') }}
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('Status History Updates Log') }}</h2>
                        <div class="space-y-4">
                            @forelse ($order->statusHistories()->latest()->get() as $history)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-green-50 text-green-700 text-xs font-bold border border-green-200">
                                            ✓
                                        </div>
                                        @if (! $loop->last)
                                            <div class="h-full w-0.5 bg-gray-100 mt-2 min-h-[20px]"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-2">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($history->status)) }}</p>
                                            <span class="text-xs text-gray-400 font-medium">{{ $history->created_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        @if ($history->remarks)
                                            <p class="text-xs text-gray-600 mt-1 italic">{{ $history->remarks }}</p>
                                        @endif
                                        @if ($history->updater)
                                            <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Updated by') }}: {{ $history->updater->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 italic">{{ __('No tracking status updates logged yet.') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Items in this order') }}</h2>
                            <span class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-500">{{ $order->items->sum('quantity') }} {{ __('items') }}</span>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <div class="flex items-center gap-4 py-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-sm font-bold text-green-700">
                                        {{ strtoupper(substr($item->product_name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-gray-900">{{ $item->product_name }}</p>
                                        <p class="text-sm text-gray-500">{{ __('Quantity') }}: {{ $item->quantity }} | {{ CurrencyService::formatLkr($item->unit_price) }}</p>
                                        @if ($item->product && $order->order_status === 'delivered' && ! \App\Models\Review::where('customer_id', Auth::user()->customer?->id)->where('order_id', $order->id)->where('product_id', $item->product_id)->exists())
                                            <a href="{{ route('customer.reviews.create', [$order, $item->product]) }}" class="mt-1 inline-block text-xs font-semibold text-green-700 underline">{{ __('Write review') }}</a>
                                        @endif
                                    </div>
                                    <p class="text-right font-bold text-gray-900">{{ CurrencyService::formatLkr($item->total_price) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Delivery address') }}</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-700">{{ $order->delivery_address }}</p>
                        @if ($order->delivery?->rider)
                            <p class="mt-3 text-sm text-gray-700">{{ __('Rider') }}: <span class="font-semibold">{{ $order->delivery->rider->user?->name }}</span></p>
                        @endif
                        @if ($googleMapsBrowserKey && $order->delivery_latitude && $order->delivery_longitude)
                            <div id="order-tracking-map" class="mt-4 h-72 rounded-2xl border border-green-100 bg-green-50"></div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Payment Summary') }}</h2>
                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ CurrencyService::formatLkr($order->subtotal) }}</dd></div>
                            <div class="flex justify-between text-green-700"><dt>{{ __('Discount') }}</dt><dd>{{ CurrencyService::formatLkr($order->discount_amount) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Delivery') }}</dt><dd>{{ CurrencyService::formatLkr($order->delivery_fee) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Service') }}</dt><dd>{{ CurrencyService::formatLkr($order->service_charge) }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 text-lg font-bold text-gray-900"><dt>{{ __('Total') }}</dt><dd>{{ CurrencyService::formatLkr($order->total_amount) }}</dd></div>
                        </dl>
                        @if ($order->payment)
                            <a href="{{ route('customer.payments.show', $order) }}" class="mt-5 inline-flex w-full justify-center rounded-full bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">{{ __('Manage payment') }}</a>
                        @endif
                    </section>

                    @if ($order->order_status === 'pending')
                        <section class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Cancel Order') }}</h2>
                            <p class="mt-2 text-sm text-gray-500">{{ __('You can cancel only while the order is pending.') }}</p>
                            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')
                                <textarea name="reason" rows="3" class="w-full rounded-2xl border-gray-200 shadow-sm focus:border-orange-500 focus:ring-orange-500" required placeholder="{{ __('Cancellation reason') }}">{{ old('reason') }}</textarea>
                                <x-danger-button>{{ __('Cancel Order') }}</x-danger-button>
                            </form>
                        </section>
                    @endif

                    @if ($order->order_status === 'delivered')
                        <section class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm sm:p-6">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Delivery completed') }}</h2>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Your receipt is ready for printing or saving.') }}</p>
                            <a href="{{ route('customer.orders.receipt', $order) }}" class="mt-4 inline-flex w-full justify-center rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">{{ __('Open Printable Receipt') }}</a>
                            @if ($order->payment?->status === 'paid')
                                <a href="{{ route('customer.refunds.create', $order) }}" class="mt-3 inline-flex w-full justify-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">{{ __('Request refund') }}</a>
                            @endif
                        </section>
                    @endif

                    <section class="rounded-3xl border border-orange-100 bg-orange-50 p-5 sm:p-6">
                        <h2 class="font-bold text-orange-800">{{ __('Need help with your order?') }}</h2>
                        <p class="mt-2 text-sm text-orange-700">{{ __('Create a support ticket for address changes, delivery issues, or payment questions.') }}</p>
                        <a href="{{ route('support.tickets.create') }}" class="mt-4 inline-flex rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">{{ __('Contact support') }}</a>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    @if ($googleMapsBrowserKey && $order->delivery_latitude && $order->delivery_longitude)
        <script>
            window.initDailyCartTrackingMap = function () {
                const delivery = { lat: {{ (float) $order->delivery_latitude }}, lng: {{ (float) $order->delivery_longitude }} };
                const rider = @json($latestLocation ? ['lat' => (float) $latestLocation->latitude, 'lng' => (float) $latestLocation->longitude] : null);
                const map = new google.maps.Map(document.getElementById('order-tracking-map'), {
                    center: rider || delivery,
                    zoom: 14,
                    mapTypeControl: false,
                    streetViewControl: false,
                });

                new google.maps.Marker({ position: delivery, map, label: 'D', title: 'Delivery address' });
                if (rider) {
                    new google.maps.Marker({ position: rider, map, label: 'R', title: 'Rider location' });
                }
            };
        </script>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsBrowserKey }}&callback=initDailyCartTrackingMap"></script>
    @endif
</x-app-layout>
