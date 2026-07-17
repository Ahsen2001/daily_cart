<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Delivery queue') }}</p><h2 class="dc-page-title">{{ __('Assigned Deliveries') }}</h2></div>
            <a href="{{ route('rider.deliveries.earnings') }}" class="dc-button-secondary">{{ __('View earnings') }}</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            <div class="dc-panel">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="dc-filter-bar mb-6 sm:grid-cols-3">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['assigned', 'picked_up', 'on_the_way', 'delivered', 'failed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Customer') }}</th><th class="px-3 py-2">{{ __('Vendor') }}</th><th class="px-3 py-2">{{ __('Scheduled') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($deliveries as $delivery)
                                @php
                                    $customerPhone = $delivery->order?->customer?->phone ?: $delivery->order?->customer?->user?->phone;
                                @endphp
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $delivery->order?->order_number }}</td>
                                    <td class="px-3 py-3">
                                        <div>{{ $delivery->order?->customer?->user?->name }}</div>
                                        @if ($customerPhone)
                                            <div class="text-xs text-gray-500">{{ __('Contact') }}: {{ $customerPhone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">{{ $delivery->order?->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ $delivery->scheduled_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-3"><x-status-badge :status="$delivery->status" /></td>
                                    <td class="px-3 py-3 text-right"><a class="font-bold text-brand-dark hover:underline" href="{{ route('rider.deliveries.show', $delivery) }}">{{ __('Update') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No assigned deliveries found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $deliveries->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
