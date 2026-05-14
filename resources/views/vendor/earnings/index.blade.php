<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Vendor Earnings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" class="grid gap-3 sm:grid-cols-4">
                    <x-text-input type="date" name="from" :value="request('from')" />
                    <x-text-input type="date" name="to" :value="request('to')" />
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </div>

            <div class="mb-6 grid gap-6 md:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Pending') }}</div><div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['pending']) }}</div></div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Completed') }}</div><div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['completed']) }}</div></div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Refunded') }}</div><div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary['refunded']) }}</div></div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Payment') }}</th><th class="px-3 py-2">{{ __('Total') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($summary['orders'] as $order)
                                <tr><td class="px-3 py-3">{{ $order->order_number }}</td><td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</td><td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($order->payment?->status ?? 'pending')) }}</td><td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">{{ __('No earnings records found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $summary['orders']->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
