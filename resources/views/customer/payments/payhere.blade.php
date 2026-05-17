<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('PayHere Checkout') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">{{ __('You are being redirected to PayHere secure checkout for an LKR payment.') }}</p>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt>{{ __('Order') }}</dt><dd class="font-semibold">{{ $payment->order->order_number }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('Amount') }}</dt><dd class="font-semibold">{{ \App\Services\CurrencyService::formatLkr($payment->amount) }}</dd></div>
                </dl>

                <form id="payhere-checkout" method="POST" action="{{ $checkoutUrl }}" class="mt-6">
                    @foreach ($payload as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <x-primary-button>{{ __('Continue to PayHere') }}</x-primary-button>
                    <a href="{{ route('customer.payments.show', $payment->order) }}" class="ms-3 text-sm font-medium text-gray-600 underline">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.setTimeout(() => document.getElementById('payhere-checkout')?.submit(), 800);
    </script>
</x-app-layout>
