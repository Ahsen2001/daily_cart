@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Sales Report') }}</h2></x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <form class="grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-6" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <select class="rounded border-gray-300" name="vendor_id">
                    <option value="">{{ __('All vendors') }}</option>
                    @foreach ($filters['vendors'] as $vendor)
                        <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->store_name }}</option>
                    @endforeach
                </select>
                <select class="rounded border-gray-300" name="payment_method">
                    <option value="">{{ __('All payment methods') }}</option>
                    @foreach (['cash_on_delivery','card','bank_transfer','wallet'] as $method)
                        <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ __(str_replace('_', ' ', $method)) }}</option>
                    @endforeach
                </select>
                <select class="rounded border-gray-300" name="order_status">
                    <option value="">{{ __('All order statuses') }}</option>
                    @foreach (['pending','confirmed','packed','assigned_to_rider','out_for_delivery','delivered','cancelled','refunded'] as $status)
                        <option value="{{ $status }}" @selected(request('order_status') === $status)>{{ __(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
            </form>

            <div class="flex flex-wrap gap-2">
                <a class="rounded bg-gray-800 px-3 py-2 text-sm text-white" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">{{ __('Export CSV') }}</a>
                <a class="rounded bg-gray-600 px-3 py-2 text-sm text-white" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">{{ __('Excel placeholder') }}</a>
                <a class="rounded bg-gray-600 px-3 py-2 text-sm text-white" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">{{ __('PDF placeholder') }}</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold text-gray-900">{{ str_contains($key, 'orders') ? number_format($value) : CurrencyService::formatLkr($value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Sales by Vendor') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($by_vendor as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row->label }}</span><span>{{ CurrencyService::formatLkr($row->revenue) }}</span></div>
                        @empty
                            <p class="text-gray-500">{{ __('No vendor sales found.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Sales by Category') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($by_category as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row->label }}</span><span>{{ CurrencyService::formatLkr($row->revenue) }}</span></div>
                        @empty
                            <p class="text-gray-500">{{ __('No category sales found.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Status') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Total') }}</th><th>{{ __('Placed') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-4 py-3">{{ $order->order_number }}</td>
                                <td>{{ $order->customer?->user?->name }}</td>
                                <td>{{ $order->vendor?->store_name }}</td>
                                <td>{{ __(str_replace('_', ' ', $order->order_status)) }}</td>
                                <td>{{ __(str_replace('_', ' ', $order->payment?->payment_method ?? '')) }}</td>
                                <td>{{ CurrencyService::formatLkr($order->total_amount) }}</td>
                                <td>{{ $order->placed_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
