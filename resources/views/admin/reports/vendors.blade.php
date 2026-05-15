@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Vendor Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Top-performing Vendors') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($top_vendors as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row->label }}</span><span>{{ $row->orders_count }} / {{ CurrencyService::formatLkr($row->revenue) }}</span></div>
                        @empty
                            <p class="text-gray-500">{{ __('No delivered vendor sales found.') }}</p>
                        @endforelse
                    </div>
                </section>
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Vendor Commission Report') }}</h3>
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($commission_report as $row)
                            <div class="flex justify-between border-b py-2"><span>{{ $row['vendor']->store_name }} ({{ number_format($row['commission_rate'], 2) }}%)</span><span>{{ CurrencyService::formatLkr($row['earnings']) }}</span></div>
                        @endforeach
                    </div>
                </section>
            </div>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-4 py-3">{{ __('Vendor') }}</th><th>{{ __('Status') }}</th><th>{{ __('Products') }}</th><th>{{ __('Pending Products') }}</th><th>{{ __('Orders') }}</th><th>{{ __('Cancellation Rate') }}</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $vendor)
                            @php
                                $cancelled = $vendor->orders()->where('order_status', 'cancelled')->count();
                                $rate = $vendor->orders_count ? ($cancelled / $vendor->orders_count) * 100 : 0;
                            @endphp
                            <tr><td class="px-4 py-3">{{ $vendor->store_name }}</td><td>{{ $vendor->status }}</td><td>{{ $vendor->products_count }}</td><td>{{ $vendor->pending_products_count }}</td><td>{{ $vendor->orders_count }}</td><td>{{ number_format($rate, 1) }}%</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $rows->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
