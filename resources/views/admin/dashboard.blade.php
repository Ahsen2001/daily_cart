@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Admin Dashboard') }}</h2>
            <x-application-logo :show-text="false" />
            <div class="flex flex-wrap gap-2 text-sm">
                <a class="rounded bg-indigo-600 px-3 py-2 text-white" href="{{ route('admin.analytics.index') }}">{{ __('Analytics') }}</a>
                <a class="rounded bg-gray-800 px-3 py-2 text-white" href="{{ route('admin.reports.sales') }}">{{ __('Reports') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">
                            @if (str_contains($key, 'revenue') || str_contains($key, 'payments') || str_contains($key, 'refunds'))
                                {{ CurrencyService::formatLkr($value) }}
                            @else
                                {{ number_format((float) $value) }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">{{ __('Revenue') }}</h3>
                    <canvas id="revenueLine" class="mt-4 h-64"></canvas>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">{{ __('Orders') }}</h3>
                    <canvas id="ordersBar" class="mt-4 h-64"></canvas>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">{{ __('Management Shortcuts') }}</h3>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <a class="rounded border px-3 py-2 text-indigo-700" href="{{ route('admin.vendors.index') }}">{{ __('Vendor approvals') }}</a>
                    <a class="rounded border px-3 py-2 text-indigo-700" href="{{ route('admin.riders.index') }}">{{ __('Rider approvals') }}</a>
                    <a class="rounded border px-3 py-2 text-indigo-700" href="{{ route('admin.products.index') }}">{{ __('Product approvals') }}</a>
                    <a class="rounded border px-3 py-2 text-indigo-700" href="{{ route('admin.support-tickets.index') }}">{{ __('Support tickets') }}</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueLine = @json($charts['revenue_line']);
        const ordersBar = @json($charts['orders_bar']);

        new Chart(document.getElementById('revenueLine'), {
            type: 'line',
            data: { labels: revenueLine.labels, datasets: [{ label: 'Revenue (LKR)', data: revenueLine.values, borderColor: '#4f46e5', tension: .3 }] },
        });

        new Chart(document.getElementById('ordersBar'), {
            type: 'bar',
            data: { labels: ordersBar.labels, datasets: [{ label: 'Orders', data: ordersBar.values, backgroundColor: '#0f766e' }] },
        });
    </script>
</x-app-layout>
