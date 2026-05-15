@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Vendor Reports') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <form class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
            </form>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold">{{ in_array($key, ['revenue', 'earnings'], true) ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Best-selling Products') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($best_selling as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row->product_name }}</span><span>{{ $row->sold_quantity }} / {{ CurrencyService::formatLkr($row->revenue) }}</span></div>
                        @endforeach
                    </div>
                </section>
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Low Stock Alerts') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($low_stock as $product)
                            <div class="flex justify-between border-b py-2"><span>{{ $product->name }}</span><span>{{ $product->stock_quantity }}</span></div>
                        @endforeach
                    </div>
                </section>
            </div>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b px-6 py-4"><h3 class="font-semibold">{{ __('Own Orders') }}</h3></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Total') }}</th><th>{{ __('Placed') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <tr><td class="px-4 py-3">{{ $order->order_number }}</td><td>{{ $order->customer?->user?->name }}</td><td>{{ $order->order_status }}</td><td>{{ $order->payment_status }}</td><td>{{ CurrencyService::formatLkr($order->total_amount) }}</td><td>{{ $order->placed_at?->format('Y-m-d H:i') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $orders->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
