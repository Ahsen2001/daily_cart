@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Upcoming Scheduled Deliveries') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <form class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
            </form>
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Rider') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <tr><td class="px-4 py-3">{{ $order->order_number }}</td><td>{{ $order->customer?->user?->name }}</td><td>{{ $order->vendor?->store_name }}</td><td>{{ $order->scheduled_delivery_at?->format('Y-m-d H:i') }}</td><td>{{ $order->delivery?->rider?->user?->name }}</td><td>{{ $order->order_status }}</td><td>{{ CurrencyService::formatLkr($order->total_amount) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
