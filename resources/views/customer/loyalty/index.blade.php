<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Loyalty Points') }}</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6 grid gap-6 md:grid-cols-3">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Available Points') }}</div><div class="mt-1 text-3xl font-semibold">{{ $balance }}</div></div>
            <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Earn Rate') }}</div><div class="mt-1 text-lg font-semibold">{{ __('Rs. :amount = 1 point', ['amount' => $setting->spend_amount_per_point]) }}</div></div>
            <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Redeem Rate') }}</div><div class="mt-1 text-lg font-semibold">{{ __('1 point = :amount', ['amount' => \App\Services\CurrencyService::formatLkr($setting->redemption_value_per_point)]) }}</div></div>
        </div>
        <div class="bg-white p-6 shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200"><thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Date') }}</th><th class="px-3 py-2">{{ __('Type') }}</th><th class="px-3 py-2">{{ __('Points') }}</th><th class="px-3 py-2">{{ __('Balance') }}</th><th class="px-3 py-2">{{ __('Description') }}</th></tr></thead><tbody class="divide-y divide-gray-100 text-sm">
                @forelse ($transactions as $transaction)
                    <tr><td class="px-3 py-3">{{ $transaction->created_at->format('M d, Y') }}</td><td class="px-3 py-3">{{ ucfirst($transaction->type) }}</td><td class="px-3 py-3">{{ $transaction->points }}</td><td class="px-3 py-3">{{ $transaction->balance_after }}</td><td class="px-3 py-3">{{ $transaction->description }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No loyalty history yet.') }}</td></tr>
                @endforelse
            </tbody></table>
            <div class="mt-6">{{ $transactions->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
