@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Vendor Dashboard') }}</h2>
            <a class="rounded bg-indigo-600 px-3 py-2 text-sm text-white" href="{{ route('vendor.reports.index') }}">{{ __('View reports') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold">{{ in_array($key, ['revenue', 'earnings'], true) ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Revenue') }}</h3>
                    <canvas id="vendorRevenue" class="mt-4 h-64"></canvas>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold">{{ __('Orders') }}</h3>
                    <canvas id="vendorOrders" class="mt-4 h-64"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const charts = @json($charts);
        new Chart(document.getElementById('vendorRevenue'), {
            type: 'line',
            data: { labels: charts.revenue_line.labels, datasets: [{ label: 'Revenue (LKR)', data: charts.revenue_line.values, borderColor: '#4f46e5', tension: .3 }] },
        });
        new Chart(document.getElementById('vendorOrders'), {
            type: 'bar',
            data: { labels: charts.orders_bar.labels, datasets: [{ label: 'Orders', data: charts.orders_bar.values, backgroundColor: '#0f766e' }] },
        });
    </script>
</x-app-layout>
