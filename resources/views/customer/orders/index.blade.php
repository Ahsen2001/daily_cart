<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('My Orders') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                <th class="px-3 py-2">{{ __('Order') }}</th>
                                <th class="px-3 py-2">{{ __('Vendor') }}</th>
                                <th class="px-3 py-2">{{ __('Scheduled Delivery') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2">{{ __('Payment') }}</th>
                                <th class="px-3 py-2">{{ __('Total') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="px-3 py-3">{{ $order->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($order->payment_status)) }}</td>
                                    <td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <a class="text-indigo-700 underline" href="{{ route('customer.orders.show', $order) }}">{{ __('Track') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('You have no orders yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
