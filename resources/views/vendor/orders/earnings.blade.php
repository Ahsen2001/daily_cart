<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Vendor Earnings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="text-sm text-gray-500">{{ __('Completed order earnings') }}</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ \App\Services\CurrencyService::formatLkr($total) }}</div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Delivered') }}</th><th class="px-3 py-2">{{ __('Amount') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($completedOrders as $order)
                                <tr><td class="px-3 py-3">{{ $order->order_number }}</td><td class="px-3 py-3">{{ $order->updated_at->format('M d, Y') }}</td><td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-6 text-center text-gray-500">{{ __('No completed orders yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $completedOrders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
