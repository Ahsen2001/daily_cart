@php
    use App\Services\CurrencyService;

    $primaryOrder = $orders->first();
    $customerName = Auth::user()->name;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Order Confirmation') }}</h2>
            <a href="{{ route('customer.orders.index') }}" class="text-sm font-semibold text-green-700 underline">{{ __('View all orders') }}</a>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8 sm:py-12">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-3xl border border-green-100 bg-white p-6 text-center shadow-sm sm:p-10">
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-green-50"></div>
                <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-orange-50"></div>
                <div class="relative">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-green-200 bg-green-50">
                        <span class="text-3xl font-bold text-green-600">✓</span>
                    </div>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.25em] text-green-700">{{ __('Order confirmed') }}</p>
                    <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ __('Thank you, :name!', ['name' => $customerName]) }}</h1>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-gray-600">
                        {{ __('Your order has been placed securely. We will notify you as the vendor confirms, packs, and hands it to a rider.') }}
                    </p>

                    @if ($primaryOrder)
                        <div class="mt-6 inline-flex flex-wrap items-center justify-center gap-2 rounded-full border border-gray-100 bg-gray-50 px-5 py-3 text-sm text-gray-600">
                            <span>{{ __('Order') }}</span>
                            <span class="font-bold text-gray-900">{{ $primaryOrder->order_number }}</span>
                            <span class="text-gray-300">|</span>
                            <span>{{ $primaryOrder->placed_at?->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </section>

            @if ($orders->isNotEmpty())
                <section class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">{{ __('Order Progress') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('Each vendor order can be tracked separately.') }}</p>
                        </div>
                        <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600">{{ __('Pending vendor confirmation') }}</span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-4">
                        @foreach ([__('Placed'), __('Confirmed'), __('Packed'), __('Delivered')] as $index => $step)
                            <div class="rounded-2xl border {{ $index === 0 ? 'border-green-200 bg-green-50' : 'border-gray-100 bg-gray-50' }} p-4 text-center">
                                <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full {{ $index === 0 ? 'bg-green-600 text-white' : 'bg-white text-gray-400' }} text-sm font-bold">{{ $index + 1 }}</div>
                                <p class="text-xs font-semibold uppercase tracking-wide {{ $index === 0 ? 'text-green-700' : 'text-gray-500' }}">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="grid gap-4">
                    @foreach ($orders as $order)
                        <article class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-green-200 hover:shadow-md sm:p-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900">{{ $order->order_number }}</h3>
                                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">{{ str_replace('_', ' ', ucfirst($order->order_status)) }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600">{{ __('Scheduled delivery') }}: <span class="font-semibold text-gray-900">{{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</span></p>
                                    <p class="text-sm text-gray-600">{{ __('Payment') }}: <span class="font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($order->payment_status)) }}</span></p>
                                </div>

                                <div class="text-left lg:text-right">
                                    <p class="text-sm text-gray-500">{{ __('Grand total') }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ CurrencyService::formatLkr($order->total_amount) }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-gray-500">{{ __('A confirmation email and in-app notification have been prepared for this order.') }}</p>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    @if ($order->payment && in_array($order->payment->payment_method, ['card', 'bank_transfer'], true) && $order->payment->status === 'pending')
                                        <a href="{{ route('customer.payments.show', $order) }}" class="inline-flex justify-center rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">{{ __('Complete Payment') }}</a>
                                    @endif
                                    <a href="{{ route('customer.orders.show', $order) }}" class="inline-flex justify-center rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">{{ __('Track Order') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @else
                <section class="rounded-3xl border border-orange-100 bg-white p-6 text-center shadow-sm">
                    <p class="text-sm text-gray-600">{{ __('No recent order details found.') }}</p>
                </section>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('customer.products.index') }}" class="inline-flex justify-center rounded-full border border-green-200 bg-white px-5 py-3 text-sm font-semibold text-green-700 transition hover:bg-green-50">{{ __('Continue Shopping') }}</a>
                <a href="{{ route('support.tickets.create') }}" class="inline-flex justify-center rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">{{ __('Need Help?') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
