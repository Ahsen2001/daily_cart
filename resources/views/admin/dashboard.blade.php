<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Admin Operations Dashboard') }}</h2>
            <x-application-logo :show-text="false" />
            <div class="flex flex-wrap gap-2 text-sm">
                <a class="rounded bg-indigo-600 px-3 py-2 text-white transition hover:bg-indigo-700" href="{{ route('admin.analytics.index') }}">{{ __('Operations Analytics') }}</a>
                <a class="rounded bg-gray-800 px-3 py-2 text-white transition hover:bg-gray-900" href="{{ route('admin.reports.sales') }}">{{ __('System Reports') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <!-- Operations Alerts / Action Needed -->
            <div class="grid gap-6 md:grid-cols-3">
                <div class="flex min-h-44 flex-col rounded-xl border border-amber-100 bg-amber-50/50 p-6 shadow-sm backdrop-blur">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-amber-900">{{ __('Pending Approvals') }}</h3>
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                            {{ $summary['pending_vendor_approvals'] + $summary['pending_rider_approvals'] + $summary['pending_product_approvals'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-amber-700">{{ __('Vendor, rider, and product approvals waiting for review.') }}</p>
                    <div class="mt-auto space-y-2 pt-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-amber-800">{{ __('Vendor Approvals') }}</span>
                            <a class="shrink-0 font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.vendors.index') }}">
                                {{ $summary['pending_vendor_approvals'] }}
                            </a>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-amber-800">{{ __('Rider Approvals') }}</span>
                            <a class="shrink-0 font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.riders.index') }}">
                                {{ $summary['pending_rider_approvals'] }}
                            </a>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-amber-800">{{ __('Product Approvals') }}</span>
                            <a class="shrink-0 font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.products.index') }}">
                                {{ $summary['pending_product_approvals'] }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex min-h-44 flex-col rounded-xl border border-red-100 bg-red-50/50 p-6 shadow-sm backdrop-blur">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-red-900">{{ __('Inventory & Stock') }}</h3>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                            {{ $summary['low_stock_products'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-red-700">{{ __('Products have reached or fallen below the warning limit of 5 units.') }}</p>
                    <div class="mt-auto pt-4">
                        <a class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.products.index') }}">
                            <span>{{ __('Manage Stock Alerts') }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="flex min-h-44 flex-col rounded-xl border border-blue-100 bg-blue-50/50 p-6 shadow-sm backdrop-blur">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-blue-900">{{ __('Support Tickets') }}</h3>
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                            {{ $summary['support_tickets_open'] }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-blue-700">{{ __('Active support queries require prompt response from the operations team.') }}</p>
                    <div class="mt-auto pt-4">
                        <a class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-800" href="{{ route('admin.support-tickets.index') }}">
                            <span>{{ __('Open Support Queue') }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- General Statistics -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Customers') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_customers']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Vendors') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_vendors']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Riders') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_riders']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Products') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_products']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Orders') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_orders']) }}</p>
                </div>
            </div>

            <!-- Daily Activity & Chart -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900">{{ __('Daily Order Analytics') }}</h3>
                        <span class="rounded bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">
                            {{ $summary['todays_orders'] }} {{ __('orders placed today') }}
                        </span>
                    </div>
                    <div class="relative h-72 max-h-72 overflow-hidden">
                        <canvas id="ordersBar" class="block h-full w-full"></canvas>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('Quick Action Center') }}</h3>
                    <div class="flex flex-col gap-3">
                        <a class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50 transition" href="{{ route('admin.orders.index') }}">
                            <span class="text-sm font-medium text-gray-700">{{ __('Track Orders') }}</span>
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50 transition" href="{{ route('admin.deliveries.index') }}">
                            <span class="text-sm font-medium text-gray-700">{{ __('Delivery Overview') }}</span>
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50 transition" href="{{ route('admin.categories.index') }}">
                            <span class="text-sm font-medium text-gray-700">{{ __('Manage Categories') }}</span>
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50 transition" href="{{ route('admin.reviews.index') }}">
                            <span class="text-sm font-medium text-gray-700">{{ __('Customer Reviews') }}</span>
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ordersBar = @json($charts['orders_bar'] ?? ['labels' => [], 'values' => []]);

        new Chart(document.getElementById('ordersBar'), {
            type: 'bar',
            data: { 
                labels: ordersBar.labels, 
                datasets: [{ 
                    label: "{{ __('Orders') }}", 
                    data: ordersBar.values, 
                    backgroundColor: '#4f46e5',
                    borderRadius: 6
                }] 
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                resizeDelay: 150,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</x-app-layout>
