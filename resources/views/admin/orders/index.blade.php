<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Order Management') }}</h2>
            <a href="{{ route('admin.deliveries.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Deliveries') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="mb-6 grid gap-3 md:grid-cols-5">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['pending', 'confirmed', 'packed', 'assigned_to_rider', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <select name="vendor_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All vendors') }}</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected((int) request('vendor_id') === $vendor->id)>{{ $vendor->store_name }}</option>
                        @endforeach
                    </select>
                    <select name="rider_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All riders') }}</option>
                        @foreach ($riders as $rider)
                            <option value="{{ $rider->id }}" @selected((int) request('rider_id') === $rider->id)>{{ $rider->user?->name }}</option>
                        @endforeach
                    </select>
                    <x-text-input type="date" name="date" :value="request('date')" />
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                <th class="px-3 py-2">{{ __('Order') }}</th>
                                <th class="px-3 py-2">{{ __('Customer') }}</th>
                                <th class="px-3 py-2">{{ __('Vendor') }}</th>
                                <th class="px-3 py-2">{{ __('Scheduled') }}</th>
                                <th class="px-3 py-2">{{ __('Rider') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2">{{ __('Total') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="px-3 py-3">{{ $order->customer?->user?->name }}</td>
                                    <td class="px-3 py-3">{{ $order->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-3">{{ $order->delivery?->rider?->user?->name ?? __('Not assigned') }}</td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</td>
                                    <td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <a class="text-indigo-700 underline" href="{{ route('admin.orders.show', $order) }}">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500">{{ __('No orders found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
