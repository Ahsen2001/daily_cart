@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Subscription Details') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status')) <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div> @endif
            <section class="rounded-lg bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div><p class="text-xs uppercase text-gray-500">{{ __('Product') }}</p><p class="font-semibold">{{ $subscription->product?->name }}</p></div>
                    <div><p class="text-xs uppercase text-gray-500">{{ __('Amount') }}</p><p class="font-semibold">{{ CurrencyService::formatLkr($subscription->total_amount) }}</p></div>
                    <div><p class="text-xs uppercase text-gray-500">{{ __('Next Delivery') }}</p><p class="font-semibold">{{ $subscription->next_delivery_date?->format('Y-m-d') }} {{ $subscription->preferred_delivery_time }}</p></div>
                    <div><p class="text-xs uppercase text-gray-500">{{ __('Status') }}</p><p class="font-semibold">{{ $subscription->status }}</p></div>
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a class="rounded bg-gray-800 px-3 py-2 text-sm text-white" href="{{ route('customer.subscriptions.edit', $subscription) }}">{{ __('Edit') }}</a>
                    @if ($subscription->status === 'active')
                        <form method="POST" action="{{ route('customer.subscriptions.pause', $subscription) }}">@csrf @method('PATCH')<button class="rounded bg-yellow-600 px-3 py-2 text-sm text-white">{{ __('Pause') }}</button></form>
                    @endif
                    @if ($subscription->status === 'paused')
                        <form method="POST" action="{{ route('customer.subscriptions.resume', $subscription) }}">@csrf @method('PATCH')<button class="rounded bg-green-600 px-3 py-2 text-sm text-white">{{ __('Resume') }}</button></form>
                    @endif
                    @if (! in_array($subscription->status, ['cancelled', 'completed'], true))
                        <form method="POST" action="{{ route('customer.subscriptions.cancel', $subscription) }}">@csrf @method('PATCH')<button class="rounded bg-red-600 px-3 py-2 text-sm text-white">{{ __('Cancel') }}</button></form>
                    @endif
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b px-6 py-4"><h3 class="font-semibold">{{ __('Generated Recurring Orders') }}</h3></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Total') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($subscription->generatedOrders as $order)
                            <tr><td class="px-4 py-3">{{ $order->order_number }}</td><td>{{ $order->order_status }}</td><td>{{ $order->payment_status }}</td><td>{{ $order->scheduled_delivery_at?->format('Y-m-d H:i') }}</td><td>{{ CurrencyService::formatLkr($order->total_amount) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</x-app-layout>
