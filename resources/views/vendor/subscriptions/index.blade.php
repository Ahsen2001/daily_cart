@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Vendor Subscriptions') }}</h2>
            <a class="rounded bg-gray-800 px-3 py-2 text-sm text-white" href="{{ route('vendor.scheduled-orders.index') }}">{{ __('Scheduled Orders') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-semibold">{{ __('Stock Requirements') }}</h3>
                <div class="mt-4 space-y-2 text-sm">
                    @foreach ($stockRequirements as $row)
                        <div class="flex justify-between border-b py-2"><span>{{ $row->product?->name }}</span><span>{{ number_format($row->required_quantity) }} {{ __('items per cycle') }}</span></div>
                    @endforeach
                </div>
            </section>
            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Customer') }}</th><th>{{ __('Product') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Next Delivery') }}</th><th>{{ __('Status') }}</th><th>{{ __('Generated Orders') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($subscriptions as $subscription)
                            <tr><td class="px-4 py-3">{{ $subscription->customer?->user?->name }}</td><td>{{ $subscription->product?->name }}</td><td>{{ $subscription->frequency }}</td><td>{{ CurrencyService::formatLkr($subscription->total_amount) }}</td><td>{{ $subscription->next_delivery_date?->format('Y-m-d') }}</td><td>{{ $subscription->status }}</td><td>{{ $subscription->generatedOrders->count() }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $subscriptions->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
