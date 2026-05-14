<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Wallet') }}</h2>
            <a href="{{ route('customer.wallet.transactions') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Transaction history') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <div class="text-sm text-gray-500">{{ __('Current Balance') }}</div>
                    <div class="mt-1 text-3xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($customer->wallet_balance) }}</div>

                    <form method="POST" action="{{ route('customer.wallet.top-up') }}" class="mt-6 space-y-3">
                        @csrf
                        <x-input-label for="amount" :value="__('Top-up amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="100" class="w-full" required />
                        <x-primary-button>{{ __('Top Up Placeholder') }}</x-primary-button>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <h3 class="mb-4 font-semibold text-gray-900">{{ __('Recent Transactions') }}</h3>
                    <div class="space-y-3">
                        @forelse ($transactions as $transaction)
                            <div class="flex justify-between border-b pb-3 text-sm last:border-b-0">
                                <div>
                                    <div class="font-medium">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</div>
                                    <div class="text-gray-500">{{ $transaction->description }}</div>
                                </div>
                                <div class="text-right">
                                    <div>{{ \App\Services\CurrencyService::formatLkr($transaction->amount) }}</div>
                                    <div class="text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">{{ __('No wallet transactions yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
