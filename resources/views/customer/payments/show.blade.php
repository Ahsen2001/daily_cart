<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Payment') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span>{{ __('Order') }}</span><span class="font-semibold">{{ $order->order_number }}</span></div>
                    <div class="flex justify-between"><span>{{ __('Method') }}</span><span>{{ str_replace('_', ' ', ucfirst($order->payment?->payment_method ?? 'pending')) }}</span></div>
                    <div class="flex justify-between"><span>{{ __('Status') }}</span><span>{{ str_replace('_', ' ', ucfirst($order->payment?->status ?? 'pending')) }}</span></div>
                    <div class="flex justify-between border-t pt-3 text-base font-semibold"><span>{{ __('Grand Total') }}</span><span>{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</span></div>
                </div>

                @if ($order->payment && in_array($order->payment->payment_method, ['card', 'bank_transfer'], true) && $order->payment->status === 'pending')
                    @if ($order->payment->payment_method === 'card')
                        <div class="mt-6">
                            <a href="{{ route('customer.payments.payhere', $order->payment) }}" class="inline-flex rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">{{ __('Pay with PayHere') }}</a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customer.payments.process', $order->payment) }}" class="mt-6 flex gap-3">
                        @csrf
                        @method('PATCH')
                        <x-primary-button name="result" value="success">{{ __('Simulate Success') }}</x-primary-button>
                        <x-danger-button name="result" value="failed">{{ __('Simulate Failure') }}</x-danger-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
