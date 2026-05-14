<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Payment Successful') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-gray-700">{{ __('Your payment has been completed.') }}</p>
                <div class="mt-4 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($payment->amount) }}</div>
                <a href="{{ route('customer.orders.show', $payment->order) }}" class="mt-6 inline-block text-sm font-medium text-indigo-700 underline">{{ __('View order') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
