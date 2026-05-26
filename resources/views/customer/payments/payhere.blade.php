@php
    use App\Services\CurrencyService;

    $order = $payment->order;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('PayHere Checkout') }}</h2>
                <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
            </div>
            <a href="{{ route('customer.payments.show', $order) }}" class="text-sm font-semibold text-green-700 underline">{{ __('Back to payment') }}</a>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-green-700">{{ __('DailyCart secure gateway') }}</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ __('Review and continue to PayHere') }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Your card details will be entered only on PayHere. DailyCart sends only the signed order payload.') }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                        <div class="rounded-full bg-green-600 px-3 py-2 text-white">{{ __('Order') }}</div>
                        <div class="rounded-full bg-green-600 px-3 py-2 text-white">{{ __('PayHere') }}</div>
                        <div class="rounded-full bg-gray-100 px-3 py-2 text-gray-500">{{ __('Done') }}</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <main class="space-y-5">
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Customer Details') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('These details are sent to PayHere for the LKR checkout session.') }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label :value="__('Name')" />
                                <x-text-input class="mt-1 block w-full bg-gray-50" type="text" :value="$order->customer?->user?->name" disabled />
                            </div>
                            <div>
                                <x-input-label :value="__('Email')" />
                                <x-text-input class="mt-1 block w-full bg-gray-50" type="email" :value="$order->customer?->user?->email" disabled />
                            </div>
                            <div>
                                <x-input-label :value="__('Phone')" />
                                <x-text-input class="mt-1 block w-full bg-gray-50" type="text" :value="$order->customer?->phone ?? $order->customer?->user?->phone" disabled />
                            </div>
                            <div>
                                <x-input-label :value="__('Country')" />
                                <x-text-input class="mt-1 block w-full bg-gray-50" type="text" value="Sri Lanka" disabled />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('PayHere Payment') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('Press continue when you are ready. The signed payload will be submitted directly to PayHere.') }}</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-green-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-green-700">{{ __('Gateway') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">PayHere</p>
                            </div>
                            <div class="rounded-2xl bg-green-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-green-700">{{ __('Currency') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">LKR</p>
                            </div>
                            <div class="rounded-2xl bg-green-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-green-700">{{ __('Mode') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">{{ config('payhere.mode') === 'live' ? __('Live') : __('Sandbox') }}</p>
                            </div>
                        </div>

                        <form id="payhere-checkout" method="POST" action="{{ $checkoutUrl }}" class="mt-6">
                            @foreach ($payload as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach

                            <div class="rounded-2xl border border-green-100 bg-green-50 p-4 text-sm leading-6 text-green-800">
                                <p class="font-semibold">{{ __('Secure handoff') }}</p>
                                <p>{{ __('You will leave DailyCart and complete the card payment on PayHere. The payment notification will update this order automatically.') }}</p>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <button type="submit" class="inline-flex justify-center rounded-full bg-gray-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">
                                    {{ __('Continue to PayHere') }}
                                </button>
                                <a href="{{ route('customer.payments.show', $order) }}" class="inline-flex justify-center rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </section>
                </main>

                <aside class="space-y-5">
                    <section class="sticky top-6 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Your Order') }}</h2>

                        <div class="mt-5 max-h-[360px] space-y-4 overflow-y-auto pr-1">
                            @foreach ($order->items as $item)
                                <div class="flex gap-3 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-sm font-bold text-green-700">
                                        {{ strtoupper(substr($item->product_name, 0, 2)) }}
                                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-900 px-1 text-[10px] font-bold text-white">{{ $item->quantity }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Qty') }} {{ $item->quantity }} | {{ CurrencyService::formatLkr($item->unit_price) }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">{{ CurrencyService::formatLkr($item->total_price) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <dl class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ CurrencyService::formatLkr($order->subtotal) }}</dd></div>
                            <div class="flex justify-between text-green-700"><dt>{{ __('Discount') }}</dt><dd>{{ CurrencyService::formatLkr($order->discount_amount) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->delivery_fee) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->service_charge) }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 text-lg font-bold text-gray-900"><dt>{{ __('PayHere Amount') }}</dt><dd>{{ CurrencyService::formatLkr($payment->amount) }}</dd></div>
                        </dl>

                        <div class="mt-5 rounded-2xl bg-gray-50 p-4 text-xs leading-5 text-gray-600">
                            <p class="font-semibold text-gray-900">{{ __('Security notice') }}</p>
                            <p class="mt-1">{{ __('DailyCart sends a signed order request to PayHere. Never share OTP, banking passwords, or card PINs with anyone.') }}</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
