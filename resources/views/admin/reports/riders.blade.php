@php use App\Services\CurrencyService; use App\Services\FinanceReportService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Rider Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-5 shadow-sm">
                <p class="text-xs uppercase text-gray-500">{{ __('Average Delivery Completion Time') }}</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($average_completion_minutes, 1) }} {{ __('minutes') }}</p>
            </div>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-semibold">{{ __('Top-performing Riders') }}</h3>
                <div class="mt-4 space-y-2 text-sm">
                    @foreach ($top_riders as $rider)
                        <div class="flex justify-between border-b py-2"><span>{{ $rider->user?->name }}</span><span>{{ $rider->completed_deliveries_count }} / {{ CurrencyService::formatLkr($rider->completed_deliveries_count * FinanceReportService::RIDER_DELIVERY_EARNING) }}</span></div>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Rider') }}</th><th>{{ __('Completed') }}</th><th>{{ __('Failed') }}</th><th>{{ __('Assignments') }}</th><th>{{ __('Earnings') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($rows as $rider)
                            <tr><td class="px-4 py-3">{{ $rider->user?->name }}</td><td>{{ $rider->completed_deliveries_count }}</td><td>{{ $rider->failed_deliveries_count }}</td><td>{{ $rider->deliveries_count }}</td><td>{{ CurrencyService::formatLkr($rider->completed_deliveries_count * FinanceReportService::RIDER_DELIVERY_EARNING) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $rows->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
