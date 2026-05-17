@php
    use App\Services\CurrencyService;

    $paidAt = $order->payment?->paid_at ?? $order->delivery?->delivered_at;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt') }} - {{ $order->order_number }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .receipt-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                max-width: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-[#F4FFF7] font-sans text-[#2D3436]">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('customer.orders.show', $order) }}" class="text-sm font-semibold text-green-700 underline">{{ __('Back to order') }}</a>
            <button type="button" onclick="window.print()" class="rounded-full bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">{{ __('Print Receipt') }}</button>
        </div>

        <section class="receipt-sheet overflow-hidden rounded-2xl bg-white shadow-xl shadow-green-900/10">
            <div class="border-b border-green-100 bg-green-50 px-6 py-6 sm:px-8">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <img src="{{ asset('images/logo.png') }}" alt="DailyCart" class="h-16 w-auto">
                        <p class="mt-3 max-w-md text-sm text-gray-600">{{ __('Smart Online Shopping & Daily Essentials Delivery Platform') }}</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-xs font-semibold uppercase tracking-wide text-green-700">{{ __('Delivery Receipt') }}</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Issued') }}: {{ now()->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 border-b border-gray-100 px-6 py-6 text-sm sm:grid-cols-3 sm:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Customer') }}</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $order->customer?->user?->name }}</p>
                    <p class="mt-1 text-gray-600">{{ $order->customer?->phone ?? $order->customer?->user?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Vendor') }}</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $order->vendor?->store_name }}</p>
                    <p class="mt-1 text-gray-600">{{ $order->vendor?->address }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Delivery') }}</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ __('Delivered') }}</p>
                    <p class="mt-1 text-gray-600">{{ $order->delivery?->delivered_at?->format('M d, Y h:i A') ?? $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</p>
                    @if ($order->delivery?->rider)
                        <p class="mt-1 text-gray-600">{{ __('Rider') }}: {{ $order->delivery->rider->user?->name }}</p>
                    @endif
                </div>
            </div>

            <div class="grid gap-6 border-b border-gray-100 px-6 py-6 text-sm sm:grid-cols-2 sm:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Delivery Address') }}</p>
                    <p class="mt-2 leading-6 text-gray-700">{{ $order->delivery_address }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Payment') }}</p>
                    <div class="mt-2 space-y-1 text-gray-700">
                        <p>{{ __('Method') }}: <span class="font-semibold">{{ str_replace('_', ' ', ucfirst($order->payment?->payment_method ?? 'pending')) }}</span></p>
                        <p>{{ __('Status') }}: <span class="font-semibold">{{ str_replace('_', ' ', ucfirst($order->payment?->status ?? $order->payment_status)) }}</span></p>
                        @if ($paidAt)
                            <p>{{ __('Paid At') }}: <span class="font-semibold">{{ $paidAt->format('M d, Y h:i A') }}</span></p>
                        @endif
                        @if ($order->payment?->transaction_reference)
                            <p>{{ __('Reference') }}: <span class="font-semibold">{{ $order->payment->transaction_reference }}</span></p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 sm:px-8">
                <h2 class="text-base font-semibold text-gray-900">{{ __('Purchased Items') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                <th class="py-3 pr-4">{{ __('Item') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Qty') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Unit Price') }}</th>
                                <th class="py-3 pl-4 text-right">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-gray-900">{{ $item->product_name }}</td>
                                    <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ CurrencyService::formatLkr($item->unit_price) }}</td>
                                    <td class="py-3 pl-4 text-right font-semibold">{{ CurrencyService::formatLkr($item->total_price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 border-t border-gray-100 px-6 py-6 sm:grid-cols-2 sm:px-8">
                <div class="text-sm text-gray-600">
                    <p class="font-semibold text-gray-900">{{ __('Thank you for shopping with DailyCart.') }}</p>
                    <p class="mt-2">{{ __('This receipt was generated after successful delivery completion.') }}</p>
                    <p class="mt-4">{{ __('Email') }}: uahsens1@gmail.com</p>
                    <p>{{ __('Phone') }}: +94 75 460 3008</p>
                    <p>{{ __('Address') }}: Oddamavadi, Sri Lanka</p>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ CurrencyService::formatLkr($order->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd>{{ CurrencyService::formatLkr($order->discount_amount) }}</dd></div>
                    @if ((float) $order->loyalty_discount_amount > 0)
                        <div class="flex justify-between"><dt>{{ __('Loyalty Discount') }}</dt><dd>{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->delivery_fee) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->service_charge) }}</dd></div>
                    <div class="flex justify-between border-t border-gray-200 pt-3 text-lg font-bold text-gray-900"><dt>{{ __('Grand Total') }}</dt><dd>{{ CurrencyService::formatLkr($order->total_amount) }}</dd></div>
                </dl>
            </div>
        </section>
    </main>
</body>
</html>
