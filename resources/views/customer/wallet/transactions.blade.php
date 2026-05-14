<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Wallet Transactions') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Date') }}</th><th class="px-3 py-2">{{ __('Type') }}</th><th class="px-3 py-2">{{ __('Direction') }}</th><th class="px-3 py-2">{{ __('Amount') }}</th><th class="px-3 py-2">{{ __('Balance After') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($transactions as $transaction)
                                <tr><td class="px-3 py-3">{{ $transaction->created_at->format('M d, Y h:i A') }}</td><td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</td><td class="px-3 py-3">{{ ucfirst($transaction->type) }}</td><td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($transaction->amount) }}</td><td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($transaction->balance_after) }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No transactions found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
