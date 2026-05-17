@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('My Subscriptions') }}</h2>
            <a class="rounded bg-indigo-600 px-4 py-2 text-sm text-white" href="{{ route('customer.subscriptions.create') }}">{{ __('Create Subscription') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status')) <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div> @endif
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Product') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Total') }}</th><th>{{ __('Next Delivery') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3">{{ $subscription->product?->name }}@if($subscription->variant) - {{ $subscription->variant->name }}@endif</td>
                                <td>{{ $subscription->frequency }}</td>
                                <td>{{ $subscription->quantity }}</td>
                                <td>{{ CurrencyService::formatLkr($subscription->total_amount) }}</td>
                                <td>{{ $subscription->next_delivery_date?->format('Y-m-d') }} {{ $subscription->preferred_delivery_time }}</td>
                                <td>{{ $subscription->status }}</td>
                                <td><a class="text-indigo-700 underline" href="{{ route('customer.subscriptions.show', $subscription) }}">{{ __('View') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $subscriptions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
