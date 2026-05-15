@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Vendor Scheduled Orders') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <tr><td class="px-4 py-3">{{ $order->order_number }}</td><td>{{ $order->customer?->user?->name }}</td><td>{{ $order->scheduled_delivery_at?->format('Y-m-d H:i') }}</td><td>{{ $order->order_status }}</td><td>{{ CurrencyService::formatLkr($order->total_amount) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
