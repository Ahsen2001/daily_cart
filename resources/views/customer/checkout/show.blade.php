<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Checkout') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-4 mb-6 text-sm font-medium text-green-700 bg-white shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-6 text-sm font-medium text-red-700 bg-white shadow-sm sm:rounded-lg">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="p-6 bg-white shadow-sm lg:col-span-2 sm:rounded-lg">
                    <form method="POST" action="{{ route('customer.checkout.store') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="coupon_code" value="{{ $couponCode }}">
                        <input type="hidden" name="loyalty_points" value="{{ $loyaltyPoints }}">

                        <div>
                            <x-input-label for="delivery_address" :value="__('Delivery Address')" />
                            <textarea id="delivery_address" name="delivery_address" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('delivery_address', Auth::user()->customer?->address_line_1.', '.Auth::user()->customer?->city.', '.Auth::user()->customer?->district) }}</textarea>
                            <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="scheduled_delivery_at" :value="__('Scheduled Delivery Time')" />
                            <x-text-input id="scheduled_delivery_at" name="scheduled_delivery_at" type="datetime-local" class="block w-full mt-1" min="{{ $minimumDeliveryTime->format('Y-m-d\TH:i') }}" :value="old('scheduled_delivery_at', $minimumDeliveryTime->format('Y-m-d\TH:i'))" required />
                            <x-input-error :messages="$errors->get('scheduled_delivery_at')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_method" :value="__('Payment Method')" />
                            <select id="payment_method" name="payment_method" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="cash_on_delivery" @selected(old('payment_method') === 'cash_on_delivery')>{{ __('Cash on Delivery') }}</option>
                                <option value="card" @selected(old('payment_method') === 'card')>{{ __('Card Payment') }}</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>{{ __('Bank Transfer') }}</option>
                                <option value="wallet" @selected(old('payment_method') === 'wallet')>{{ __('Wallet') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>

                        <x-primary-button>{{ __('Place Order') }}</x-primary-button>
                    </form>
                </div>

                <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">{{ __('Order Summary') }}</h3>

                    <form method="POST" action="{{ route('customer.checkout.coupon') }}" class="flex gap-2 mt-4">
                        @csrf
                        <x-text-input name="coupon_code" placeholder="Coupon code" :value="$couponCode" />
                        <x-secondary-button>{{ __('Apply') }}</x-secondary-button>
                    </form>

                    <form method="POST" action="{{ route('customer.checkout.loyalty') }}" class="flex gap-2 mt-4">
                        @csrf
                        <x-text-input name="loyalty_points" type="number" min="0" placeholder="Loyalty points" :value="$loyaltyPoints" />
                        <x-secondary-button>{{ __('Redeem') }}</x-secondary-button>
                    </form>

                    <dl class="mt-6 space-y-3 text-sm">
                        <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['subtotal']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['discount']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Loyalty Discount') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['loyalty_discount']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['delivery_fee']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['service_charge']) }}</dd></div>
                        <div class="flex justify-between border-t border-gray-100 pt-3 text-base font-semibold"><dt>{{ __('Grand Total') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['grand_total']) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
