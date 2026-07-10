@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Super Admin Financial Dashboard') }}</h2>
            <x-application-logo :show-text="false" />
            <div class="flex flex-wrap gap-2 text-sm">
                <a class="rounded bg-indigo-600 px-3 py-2 text-white transition hover:bg-indigo-700" href="{{ route('admin.analytics.index') }}">{{ __('System Analytics') }}</a>
                <a class="rounded bg-gray-800 px-3 py-2 text-white transition hover:bg-gray-900" href="{{ route('admin.reports.sales') }}">{{ __('Financial Reports') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <!-- High Level Financial Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Total Revenue') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-indigo-600">{{ CurrencyService::formatLkr($summary['total_revenue']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Today\'s Revenue') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-emerald-600">{{ CurrencyService::formatLkr($summary['todays_revenue']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Vendor Payouts') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-amber-600">{{ CurrencyService::formatLkr($summary['total_vendor_payouts']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Rider Payouts') }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-cyan-600">{{ CurrencyService::formatLkr($summary['total_rider_payouts']) }}</p>
                </div>
            </div>

            <!-- Supporting Financial Metrics & Promos -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase">{{ __('Pending COD Payments') }}</p>
                    <p class="mt-2 text-xl font-bold text-gray-800">{{ CurrencyService::formatLkr($summary['pending_cod_payments']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase">{{ __('Approved Refunds') }}</p>
                    <p class="mt-2 text-xl font-bold text-red-600">{{ CurrencyService::formatLkr($summary['total_refunds']) }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase">{{ __('Active Campaigns') }}</p>
                    <p class="mt-2 text-xl font-bold text-gray-800">{{ $summary['active_promotions'] }} {{ __('Promotions') }}</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase">{{ __('Active Coupons') }}</p>
                    <p class="mt-2 text-xl font-bold text-gray-800">{{ $summary['active_coupons'] }} {{ __('Coupons') }}</p>
                </div>
            </div>

            <!-- Double Column Charts -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('Revenue Trend') }}</h3>
                    <div class="h-72">
                        <canvas id="revenueLine"></canvas>
                    </div>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4">{{ __('Order Volume') }}</h3>
                    <div class="h-72">
                        <canvas id="ordersBar"></canvas>
                    </div>
                </div>
            </div>

            <!-- Management Section -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-4">{{ __('Global Management Console') }}</h3>
                <div class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('admin.finance.index') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Financial Dashboard') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Detailed payouts, charges, and commission tracking.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Access Console') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('admin.refunds.index') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Refund Processing') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Approve or reject customer refund requests.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Access Console') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('admin.loyalty-settings.edit') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Loyalty & Rules') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Configure customer loyalty settings & multiplier rates.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Configure Rules') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('admin.promotions.index') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Promotional Campaigns') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Administer campaigns, banners, and discounts.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Manage Campaigns') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('super-admin.admins.index') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Admin Management') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Create, edit, suspend, and delete platform admins.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Manage Admins') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('super-admin.settings.index') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Platform Settings') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Configure SMTP, PayHere, Firebase, currency & maintenance.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('Access Settings') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('super-admin.logs.activity') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Activity Logs') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Audit platform operations and system log events.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('View Logs') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('super-admin.logs.api') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('API Integration Logs') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Track system integrations and external APIs.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('View API Logs') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a class="flex flex-col justify-between rounded-xl border border-gray-100 p-4 hover:bg-gray-50 transition" href="{{ route('super-admin.logs.security') }}">
                        <div>
                            <p class="font-bold text-gray-800">{{ __('Security Logs') }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Monitor auth failures, password resets & suspensions.') }}</p>
                        </div>
                        <span class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                            {{ __('View Security Logs') }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueLine = @json($charts['revenue_line'] ?? ['labels' => [], 'values' => []]);
        const ordersBar = @json($charts['orders_bar'] ?? ['labels' => [], 'values' => []]);

        new Chart(document.getElementById('revenueLine'), {
            type: 'line',
            data: { 
                labels: revenueLine.labels, 
                datasets: [{ 
                    label: "{{ __('Revenue (LKR)') }}", 
                    data: revenueLine.values, 
                    borderColor: '#4f46e5', 
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    fill: true,
                    tension: .3,
                    borderWidth: 2
                }] 
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('ordersBar'), {
            type: 'bar',
            data: { 
                labels: ordersBar.labels, 
                datasets: [{ 
                    label: "{{ __('Orders') }}", 
                    data: ordersBar.values, 
                    backgroundColor: '#0f766e',
                    borderRadius: 4
                }] 
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
