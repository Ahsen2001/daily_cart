<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Payment Failed') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-gray-700">{{ __('The payment could not be completed. You can retry from the payment page.') }}</p>
                <a href="{{ route('customer.payments.show', $payment->order) }}" class="mt-6 inline-block text-sm font-medium text-indigo-700 underline">{{ __('Back to payment') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
