<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Request Refund') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="mb-6 text-sm">
                    <div class="flex justify-between"><span>{{ __('Order') }}</span><span class="font-semibold">{{ $order->order_number }}</span></div>
                    <div class="flex justify-between"><span>{{ __('Paid Amount') }}</span><span class="font-semibold">{{ \App\Services\CurrencyService::formatLkr($order->payment?->amount ?? 0) }}</span></div>
                </div>

                <form method="POST" action="{{ route('customer.refunds.store', $order) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="amount" :value="__('Refund amount')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="1" max="{{ $order->payment?->amount }}" class="mt-1 w-full" required />
                    </div>
                    <div>
                        <x-input-label for="reason" :value="__('Reason')" />
                        <textarea id="reason" name="reason" rows="5" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>{{ old('reason') }}</textarea>
                    </div>
                    <x-primary-button>{{ __('Submit Refund Request') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
