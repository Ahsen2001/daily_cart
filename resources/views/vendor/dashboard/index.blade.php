@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Store overview') }}</p><h2 class="dc-page-title">{{ __('Vendor Dashboard') }}</h2></div>
            <a class="dc-button" href="{{ route('vendor.reports.index') }}">{{ __('View reports') }}</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="dc-card border-l-4 border-l-brand-primary p-5">
                        <p class="dc-page-eyebrow">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-brand-text">{{ in_array($key, ['revenue', 'earnings'], true) ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="dc-panel">
                    <h3 class="text-lg font-bold">{{ __('Revenue trend') }}</h3>
                    <p class="mt-1 text-sm text-brand-muted">{{ __('Track recent store revenue in LKR.') }}</p>
                    <canvas id="vendorRevenue" class="mt-4 h-64" role="img" aria-label="{{ __('Revenue trend chart') }}"></canvas>
                </div>
                <div class="dc-panel">
                    <h3 class="text-lg font-bold">{{ __('Order volume') }}</h3>
                    <p class="mt-1 text-sm text-brand-muted">{{ __('Compare the number of orders over time.') }}</p>
                    <canvas id="vendorOrders" class="mt-4 h-64" role="img" aria-label="{{ __('Order volume chart') }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const charts = @json($charts);
        new Chart(document.getElementById('vendorRevenue'), {
            type: 'line',
            data: { labels: charts.revenue_line.labels, datasets: [{ label: 'Revenue (LKR)', data: charts.revenue_line.values, borderColor: '#15803D', backgroundColor: 'rgba(21, 128, 61, .08)', fill: true, tension: .3 }] },
            options: { responsive: true, maintainAspectRatio: false },
        });
        new Chart(document.getElementById('vendorOrders'), {
            type: 'bar',
            data: { labels: charts.orders_bar.labels, datasets: [{ label: 'Orders', data: charts.orders_bar.values, backgroundColor: '#15803D', borderRadius: 8 }] },
            options: { responsive: true, maintainAspectRatio: false },
        });
    </script>
</x-app-layout>
