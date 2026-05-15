@php use App\Services\CurrencyService; @endphp

<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Customer Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">{{ __('Active Customers') }}</p><p class="mt-2 text-2xl font-bold">{{ number_format($active_customers) }}</p></div>
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">{{ __('Repeat Customers') }}</p><p class="mt-2 text-2xl font-bold">{{ number_format($repeat_customers) }}</p></div>
                <div class="rounded-lg bg-white p-5 shadow-sm"><p class="text-xs uppercase text-gray-500">{{ __('Customer Support Count') }}</p><p class="mt-2 text-2xl font-bold">{{ number_format($support_counts->sum('support_tickets_count')) }}</p></div>
            </div>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="font-semibold">{{ __('Top-spending Customers') }}</h3>
                <div class="mt-4 space-y-2 text-sm">
                    @foreach ($top_spending as $customer)
                        <div class="flex justify-between border-b py-2"><span>{{ $customer->user?->name ?? trim($customer->first_name.' '.$customer->last_name) }}</span><span>{{ CurrencyService::formatLkr($customer->paid_total ?? 0) }}</span></div>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b px-6 py-4"><h3 class="font-semibold">{{ __('New Customers') }}</h3></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Name') }}</th><th>{{ __('Phone') }}</th><th>{{ __('City') }}</th><th>{{ __('Joined') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($new_customers as $customer)
                            <tr><td class="px-4 py-3">{{ $customer->user?->name ?? trim($customer->first_name.' '.$customer->last_name) }}</td><td>{{ $customer->phone }}</td><td>{{ $customer->city }}</td><td>{{ $customer->created_at?->format('Y-m-d') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $new_customers->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
