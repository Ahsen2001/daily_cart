@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Rider Reports') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <form class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
            </form>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold">{{ str_contains($key, 'earnings') ? CurrencyService::formatLkr($value) : number_format($value) }}</p>
                    </div>
                @endforeach
            </div>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-semibold">{{ __('Earnings') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('Completed deliveries are paid at') }} {{ CurrencyService::formatLkr($earnings['per_delivery']) }} {{ __('each.') }}</p>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Status') }}</th><th>{{ __('Scheduled') }}</th><th>{{ __('Delivered') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($deliveries as $delivery)
                            <tr><td class="px-4 py-3">{{ $delivery->order?->order_number }}</td><td>{{ $delivery->order?->customer?->user?->name }}</td><td>{{ $delivery->status }}</td><td>{{ $delivery->scheduled_at?->format('Y-m-d H:i') }}</td><td>{{ $delivery->delivered_at?->format('Y-m-d H:i') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $deliveries->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
