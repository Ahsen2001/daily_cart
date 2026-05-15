<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Analytics') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <form class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-5" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <select class="rounded border-gray-300" name="vendor_id">
                    <option value="">{{ __('All vendors') }}</option>
                    @foreach ($filters['vendors'] as $vendor)
                        <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->store_name }}</option>
                    @endforeach
                </select>
                <select class="rounded border-gray-300" name="order_status">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['pending','confirmed','packed','assigned_to_rider','out_for_delivery','delivered','cancelled','refunded'] as $status)
                        <option value="{{ $status }}" @selected(request('order_status') === $status)>{{ __(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
            </form>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ([
                    'revenue_line' => 'Revenue line chart',
                    'orders_bar' => 'Orders bar chart',
                    'category_sales_pie' => 'Category sales pie chart',
                    'payment_method_chart' => 'Payment method chart',
                ] as $key => $title)
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">{{ __($title) }}</h3>
                        <canvas id="{{ $key }}" class="mt-4 h-64"></canvas>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const charts = @json($charts);
        const chartTypes = {
            revenue_line: 'line',
            orders_bar: 'bar',
            category_sales_pie: 'pie',
            payment_method_chart: 'doughnut',
        };

        Object.keys(charts).forEach((key) => {
            new Chart(document.getElementById(key), {
                type: chartTypes[key],
                data: {
                    labels: charts[key].labels,
                    datasets: [{ label: key.replaceAll('_', ' '), data: charts[key].values }]
                },
            });
        });
    </script>
</x-app-layout>
