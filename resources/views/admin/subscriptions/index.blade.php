@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Subscription Dashboard') }}</h2>
            <a class="rounded bg-gray-800 px-3 py-2 text-sm text-white" href="{{ route('admin.subscriptions.products') }}">{{ __('Eligible Products') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status')) <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div> @endif
            <div class="rounded-lg bg-white p-4 shadow-sm">
                <p class="text-sm text-gray-700">{{ __('Failed recurring order checks') }}: <span class="font-semibold">{{ $failedCount }}</span></p>
            </div>
            <form class="grid gap-3 rounded-lg bg-white p-4 shadow-sm md:grid-cols-6" method="GET">
                <select class="rounded border-gray-300" name="vendor_id"><option value="">{{ __('All vendors') }}</option>@foreach ($vendors as $vendor)<option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->store_name }}</option>@endforeach</select>
                <select class="rounded border-gray-300" name="product_id"><option value="">{{ __('All products') }}</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>@endforeach</select>
                <select class="rounded border-gray-300" name="frequency"><option value="">{{ __('All frequencies') }}</option>@foreach (['daily','weekly','monthly'] as $frequency)<option value="{{ $frequency }}" @selected(request('frequency') === $frequency)>{{ $frequency }}</option>@endforeach</select>
                <select class="rounded border-gray-300" name="status"><option value="">{{ __('All statuses') }}</option>@foreach (['active','paused','cancelled','completed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
                <a class="rounded bg-gray-100 px-4 py-2 text-center text-gray-700" href="{{ route('admin.scheduled-orders.index') }}">{{ __('Scheduled Orders') }}</a>
            </form>
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Customer') }}</th><th>{{ __('Product') }}</th><th>{{ __('Vendor') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Next') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3">{{ $subscription->customer?->user?->name }}</td><td>{{ $subscription->product?->name }}</td><td>{{ $subscription->vendor?->store_name }}</td><td>{{ $subscription->frequency }}</td><td>{{ CurrencyService::formatLkr($subscription->total_amount) }}</td><td>{{ $subscription->next_delivery_date?->format('Y-m-d') }}</td><td>{{ $subscription->status }}</td>
                                <td class="space-x-2">
                                    <form class="inline" method="POST" action="{{ route('admin.subscriptions.pause', $subscription) }}">@csrf @method('PATCH')<button class="text-yellow-700 underline">{{ __('Pause') }}</button></form>
                                    <form class="inline" method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}">@csrf @method('PATCH')<button class="text-red-700 underline">{{ __('Cancel') }}</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $subscriptions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
