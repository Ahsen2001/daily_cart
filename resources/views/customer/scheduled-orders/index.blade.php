@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Scheduled Future Orders') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status')) <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div> @endif
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th><th></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-4 py-3">{{ $order->order_number }}</td><td>{{ $order->vendor?->store_name }}</td><td>{{ $order->scheduled_delivery_at?->format('Y-m-d H:i') }}</td><td>{{ $order->order_status }}</td><td>{{ CurrencyService::formatLkr($order->total_amount) }}</td>
                                <td>@if ($order->order_status === 'pending')<form method="POST" action="{{ route('customer.scheduled-orders.cancel', $order) }}">@csrf @method('PATCH')<button class="text-red-700 underline">{{ __('Cancel') }}</button></form>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
