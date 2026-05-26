@php
    use App\Services\CurrencyService;

    $payment = $order->payment;
    $method = $payment?->payment_method ?? 'pending';
    $status = $payment?->status ?? $order->payment_status;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Secure Payment') }}</h2>
                <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
            </div>
            <a href="{{ route('customer.orders.show', $order) }}" class="text-sm font-semibold text-green-700 underline">{{ __('Back to order') }}</a>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-white p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <div class="mb-6 rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-green-700">{{ __('DailyCart payment') }}</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ __('Complete your LKR payment') }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Card details are handled by PayHere. DailyCart does not store card numbers.') }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                        <div class="rounded-full bg-green-600 px-3 py-2 text-white">{{ __('Order') }}</div>
                        <div class="rounded-full {{ $status === 'pending' ? 'bg-green-600 text-white' : 'bg-gray-900 text-white' }} px-3 py-2">{{ __('Payment') }}</div>
                        <div class="rounded-full {{ $status === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-500' }} px-3 py-2">{{ __('Done') }}</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <main class="space-y-5">
                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5">
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Payment Method') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('Choose a payment option for this order. Paid and refunded payments cannot be changed.') }}</p>
                        </div>

                        <form method="POST" action="{{ $payment ? route('customer.payments.method', $payment) : '#' }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                'cash_on_delivery' => [__('Cash on Delivery'), __('Pay the rider after delivery is completed.')],
                                'card' => [__('Card Payment'), __('Redirects securely to PayHere for LKR payments.')],
                                'bank_transfer' => [__('Bank Transfer'), __('Placeholder processing for manual bank payments.')],
                                'wallet' => [__('Wallet'), __('Processed from your DailyCart wallet during checkout.')],
                            ] as $value => [$label, $description])
                                <label class="cursor-pointer rounded-2xl border {{ $method === $value ? 'border-green-300 bg-green-50' : 'border-gray-100 bg-gray-50' }} p-4 transition hover:border-green-300 hover:bg-green-50">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="flex items-center gap-2 text-sm font-bold text-gray-900">
                                            <input type="radio" name="payment_method" value="{{ $value }}" class="text-green-600 focus:ring-green-500" @checked($method === $value) @disabled(! $payment || in_array($status, ['paid', 'refunded'], true))>
                                            {{ $label }}
                                        </span>
                                        @if ($method === $value)
                                            <span class="rounded-full bg-green-600 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white">{{ __('Selected') }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs leading-5 text-gray-600">{{ $description }}</p>
                                </label>
                            @endforeach
                            </div>

                            @if ($payment && ! in_array($status, ['paid', 'refunded'], true))
                                <button type="submit" class="inline-flex justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700">
                                    {{ __('Update Payment Method') }}
                                </button>
                            @endif
                        </form>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ __('Payment Status') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('Current payment state for this order.') }}</p>
                            </div>
                            <span class="rounded-full {{ $status === 'paid' ? 'bg-green-50 text-green-700' : ($status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-orange-50 text-orange-700') }} px-3 py-1 text-xs font-bold uppercase tracking-wide">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Order') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">{{ $order->order_number }}</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Currency') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">LKR</p>
                            </div>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Amount') }}</p>
                                <p class="mt-1 font-semibold text-gray-900">{{ CurrencyService::formatLkr($payment?->amount ?? $order->total_amount) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Secure Action') }}</h2>

                        @if (! $payment)
                            <p class="mt-3 rounded-2xl bg-orange-50 p-4 text-sm text-orange-700">{{ __('No payment record is available for this order yet.') }}</p>
                        @elseif ($status === 'paid')
                            <div class="mt-4 rounded-2xl bg-green-50 p-4 text-sm text-green-800">{{ __('Payment is completed. You can continue tracking your delivery.') }}</div>
                            <a href="{{ route('customer.orders.show', $order) }}" class="mt-5 inline-flex rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700">{{ __('Track Order') }}</a>
                        @elseif ($method === 'cash_on_delivery')
                            <div class="mt-4 rounded-2xl bg-orange-50 p-4 text-sm text-orange-700">{{ __('Cash on Delivery payments remain pending until the rider marks the order as delivered.') }}</div>
                        @elseif ($method === 'wallet')
                            <div class="mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">{{ __('Wallet payments are processed during checkout. Please check your wallet transaction history if this remains pending.') }}</div>
                        @elseif ($method === 'card')
                            <div class="mt-4 rounded-2xl bg-green-50 p-4 text-sm text-green-800">{{ __('You will be redirected to PayHere secure checkout. Card information is entered only on PayHere.') }}</div>
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                @if ($payment->status === 'pending')
                                    <a href="{{ route('customer.payments.payhere', $payment) }}" class="inline-flex justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700">{{ __('Pay with PayHere') }}</a>
                                @endif
                                @can('process', $payment)
                                    <form method="POST" action="{{ route('customer.payments.process', $payment) }}" class="flex flex-col gap-3 sm:flex-row">
                                        @csrf
                                        @method('PATCH')
                                        <x-secondary-button type="submit" name="result" value="success">{{ __('Simulate Success') }}</x-secondary-button>
                                        <x-danger-button name="result" value="failed">{{ __('Simulate Failure') }}</x-danger-button>
                                    </form>
                                @endcan
                            </div>
                        @elseif ($method === 'bank_transfer')
                            <div class="mt-4 space-y-3 rounded-2xl bg-orange-50 p-4 text-sm text-orange-800">
                                <p class="font-bold text-gray-900">{{ __('Bank transfer details') }}</p>
                                <p>{{ __('Peoples Bank') }}<br>{{ __('Account') }}: 167200230025623<br>UMER AHSEN</p>
                                <p>{{ __('Commercial Bank') }}<br>UMER AHSEN<br>{{ __('Account') }}: 8018339778<br>{{ __('Branch') }}: 159 - Valaichchenai</p>
                                <p>{{ __('Amana Bank') }}<br>{{ __('Account') }}: 0110118699003</p>
                            </div>
                            @can('process', $payment)
                                <form method="POST" action="{{ route('customer.payments.process', $payment) }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                                    @csrf
                                    @method('PATCH')
                                    <x-primary-button name="result" value="success">{{ __('Simulate Success') }}</x-primary-button>
                                    <x-danger-button name="result" value="failed">{{ __('Simulate Failure') }}</x-danger-button>
                                </form>
                            @endcan
                        @else
                            <p class="mt-3 rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">{{ __('This payment method does not require action here.') }}</p>
                        @endif
                    </section>
                </main>

                <aside class="space-y-5">
                    <section class="sticky top-6 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Order Summary') }}</h2>

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
                            @if ((float) $order->loyalty_discount_amount > 0)
                                <div class="flex justify-between text-green-700"><dt>{{ __('Loyalty Discount') }}</dt><dd>{{ CurrencyService::formatLkr($order->loyalty_discount_amount) }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->delivery_fee) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ CurrencyService::formatLkr($order->service_charge) }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 text-lg font-bold text-gray-900"><dt>{{ __('Grand Total') }}</dt><dd>{{ CurrencyService::formatLkr($order->total_amount) }}</dd></div>
                        </dl>

                        <div class="mt-5 rounded-2xl bg-gray-50 p-4 text-xs leading-5 text-gray-600">
                            <p class="font-semibold text-gray-900">{{ __('Security notice') }}</p>
                            <p class="mt-1">{{ __('DailyCart never asks you to enter card numbers on this page. Use PayHere for card payments.') }}</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
