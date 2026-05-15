@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Finance Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <form class="flex flex-wrap gap-3 rounded-lg bg-white p-4 shadow-sm" method="GET">
                <input class="rounded border-gray-300" type="date" name="from" value="{{ request('from') }}">
                <input class="rounded border-gray-300" type="date" name="to" value="{{ request('to') }}">
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ __('Filter') }}</button>
                <a class="rounded bg-gray-800 px-4 py-2 text-white" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">{{ __('Export CSV') }}</a>
                <a class="rounded bg-gray-600 px-4 py-2 text-white" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">{{ __('PDF placeholder') }}</a>
            </form>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold">{{ str_contains($key, 'orders') ? number_format($value) : CurrencyService::formatLkr($value) }}</p>
                    </div>
                @endforeach
            </div>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b px-6 py-4"><h3 class="font-semibold">{{ __('Refunds') }}</h3></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Order') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Processed') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($refunds as $refund)
                            <tr><td class="px-4 py-3">{{ $refund->order?->order_number }}</td><td>{{ $refund->order?->customer?->user?->name }}</td><td>{{ CurrencyService::formatLkr($refund->amount) }}</td><td>{{ $refund->status }}</td><td>{{ $refund->processed_at?->format('Y-m-d H:i') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $refunds->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
