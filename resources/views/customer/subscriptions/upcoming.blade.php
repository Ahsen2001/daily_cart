<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Upcoming Deliveries') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Product') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Date') }}</th><th>{{ __('Time') }}</th><th>{{ __('Address') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($subscriptions as $subscription)
                            <tr><td class="px-4 py-3">{{ $subscription->product?->name }}@if($subscription->variant) - {{ $subscription->variant->name }}@endif</td><td>{{ $subscription->vendor?->store_name }}</td><td>{{ $subscription->next_delivery_date?->format('Y-m-d') }}</td><td>{{ $subscription->preferred_delivery_time }}</td><td>{{ $subscription->delivery_address }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $subscriptions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
