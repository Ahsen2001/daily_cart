<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Delivery Fees Configuration') }}</h2>
            <a href="{{ route('admin.delivery-fees.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700">
                {{ __('Add Configuration') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-950 shadow-sm">
                <p class="font-bold">{{ __('These rules are the authoritative delivery prices for DailyCart checkout.') }}</p>
                <p class="mt-2 leading-6">{{ __('Active district rules apply to web checkout, API quotes, order creation, payments, and recurring orders. The delivery fee is calculated once for the entire checkout, not per product or vendor order. Base Fee is always charged; Per KM Fee is added when distance is available. Minimum Order and Free Delivery Limit are evaluated against the checkout subtotal.') }}</p>
                <p class="mt-2 leading-6">{{ __('Use “All Districts”, “Default”, or “*” as an active fallback rule for locations without an exact district match. Only Admin and Super Admin accounts can manage these configurations.') }}</p>
            </div>

            <section class="mb-6 rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ __('Service Charge Configuration') }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Calculated once from the checkout subtotal, then retained across the created orders and payments.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.delivery-fees.service-charge.update') }}" class="grid w-full max-w-3xl gap-3 sm:grid-cols-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="service_charge_rate_percent" :value="__('Service Charge (%)')" />
                            <x-text-input id="service_charge_rate_percent" name="service_charge_rate_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('service_charge_rate_percent', number_format($serviceChargeRatePercent, 2, '.', ''))" required />
                            <x-input-error :messages="$errors->get('service_charge_rate_percent')" class="mt-2" />
                        </div>
                        <div><x-input-label for="service_charge_flat_amount" :value="__('Fixed (LKR)')" /><x-text-input id="service_charge_flat_amount" name="service_charge_flat_amount" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('service_charge_flat_amount', $financialPolicy['service_charge_flat_amount'])" required /></div>
                        <div><x-input-label for="service_charge_minimum" :value="__('Minimum (LKR)')" /><x-text-input id="service_charge_minimum" name="service_charge_minimum" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('service_charge_minimum', $financialPolicy['service_charge_minimum'])" required /></div>
                        <div><x-input-label for="service_charge_maximum" :value="__('Maximum (0 = none)')" /><x-text-input id="service_charge_maximum" name="service_charge_maximum" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('service_charge_maximum', $financialPolicy['service_charge_maximum'])" required /></div>
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </form>
                </div>
            </section>

            <section class="mb-6 rounded-2xl border border-sky-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="text-sm font-bold text-gray-900">{{ __('Delivery Promotion') }}</p><p class="mt-1 text-sm text-gray-500">{{ __('Apply one checkout-wide delivery discount after the district rule is calculated. Set the percentage to 0 to disable it.') }}</p></div>
                    <form method="POST" action="{{ route('admin.delivery-fees.delivery-promotion.update') }}" class="grid w-full max-w-xl gap-3 sm:grid-cols-3">@csrf @method('PUT')
                        <div><x-input-label for="delivery_promotion_discount_percent" :value="__('Discount (%)')" /><x-text-input id="delivery_promotion_discount_percent" name="delivery_promotion_discount_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['delivery_promotion_discount_percent']" required /></div>
                        <div><x-input-label for="delivery_promotion_minimum_subtotal" :value="__('Minimum cart (LKR)')" /><x-text-input id="delivery_promotion_minimum_subtotal" name="delivery_promotion_minimum_subtotal" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['delivery_promotion_minimum_subtotal']" required /></div>
                        <x-primary-button class="self-end">{{ __('Save promotion') }}</x-primary-button>
                    </form>
                </div>
            </section>

            <section class="mb-6 rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <div class="mb-4"><p class="text-sm font-bold text-gray-900">{{ __('Financial Policies') }}</p><p class="mt-1 text-sm text-gray-500">{{ __('Rider payouts and the default vendor commission are controlled by Super Admin. Each delivered order retains its calculated rider payout.') }}</p></div>
                @if (Auth::user()->isSuperAdmin() || Auth::user()->can('delivery.rider_payouts.manage') || Auth::user()->can('finance.commissions.manage'))
                    <div class="grid gap-6 lg:grid-cols-2">
                        @if (Auth::user()->isSuperAdmin() || Auth::user()->can('delivery.rider_payouts.manage'))
                        <form method="POST" action="{{ route('admin.delivery-fees.rider-payout.update') }}" class="grid gap-3 sm:grid-cols-2">@csrf @method('PUT')
                            <div class="sm:col-span-2 font-semibold">{{ __('Rider payout rule') }}</div>
                            <div><x-input-label for="rider_payout_base" :value="__('Base per delivery (LKR)')" /><x-text-input id="rider_payout_base" name="rider_payout_base" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['rider_payout_base']" required /></div>
                            <div><x-input-label for="rider_payout_per_km" :value="__('Per KM (LKR)')" /><x-text-input id="rider_payout_per_km" name="rider_payout_per_km" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['rider_payout_per_km']" required /></div>
                            <div><x-input-label for="rider_peak_bonus" :value="__('Peak bonus (LKR)')" /><x-text-input id="rider_peak_bonus" name="rider_peak_bonus" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['rider_peak_bonus']" required /></div>
                            <div class="grid grid-cols-2 gap-2"><div><x-input-label for="rider_peak_start_hour" :value="__('Peak starts')" /><x-text-input id="rider_peak_start_hour" name="rider_peak_start_hour" type="number" min="0" max="23" class="mt-1 block w-full" :value="$financialPolicy['rider_peak_start_hour']" required /></div><div><x-input-label for="rider_peak_end_hour" :value="__('Peak ends')" /><x-text-input id="rider_peak_end_hour" name="rider_peak_end_hour" type="number" min="0" max="23" class="mt-1 block w-full" :value="$financialPolicy['rider_peak_end_hour']" required /></div></div>
                            <x-primary-button class="w-fit">{{ __('Save rider rule') }}</x-primary-button>
                        </form>
                        @endif
                        @if (Auth::user()->isSuperAdmin())
                        <form method="POST" action="{{ route('admin.delivery-fees.vendor-commission.update') }}" class="space-y-3">@csrf @method('PUT')
                            <p class="font-semibold">{{ __('Default vendor commission') }}</p>
                            <div><x-input-label for="default_vendor_commission_rate" :value="__('Commission (%)')" /><x-text-input id="default_vendor_commission_rate" name="default_vendor_commission_rate" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['default_vendor_commission_rate']" required /></div>
                            <x-primary-button>{{ __('Save commission') }}</x-primary-button>
                        </form>
                        @elseif (Auth::user()->can('finance.commissions.manage'))
                        <form method="POST" action="{{ route('admin.delivery-fees.vendor-commission.update') }}" class="space-y-3">@csrf @method('PUT')
                            <p class="font-semibold">{{ __('Default vendor commission') }}</p>
                            <div><x-input-label for="default_vendor_commission_rate" :value="__('Commission (%)')" /><x-text-input id="default_vendor_commission_rate" name="default_vendor_commission_rate" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="$financialPolicy['default_vendor_commission_rate']" required /></div>
                            <x-primary-button>{{ __('Save commission') }}</x-primary-button>
                        </form>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-amber-800">{{ __('Only Super Admin can change rider payout and default commission rules.') }}</p>
                @endif
            </section>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold">
                            <th class="p-4">{{ __('District') }}</th>
                            <th class="p-4">{{ __('Base Fee') }}</th>
                            <th class="p-4">{{ __('Per KM Fee') }}</th>
                            <th class="p-4">{{ __('Minimum Order') }}</th>
                            <th class="p-4">{{ __('Free Delivery limit') }}</th>
                            <th class="p-4">{{ __('Status') }}</th>
                            <th class="p-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse ($fees as $fee)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-semibold text-gray-900">{{ $fee->district }}</td>
                                <td class="p-4 font-mono">{{ \App\Services\CurrencyService::formatLkr($fee->base_fee) }}</td>
                                <td class="p-4 font-mono">{{ \App\Services\CurrencyService::formatLkr($fee->per_km_fee) }}</td>
                                <td class="p-4 font-mono">{{ \App\Services\CurrencyService::formatLkr($fee->minimum_order) }}</td>
                                <td class="p-4 font-mono">{{ $fee->free_delivery_limit !== null ? \App\Services\CurrencyService::formatLkr($fee->free_delivery_limit) : __('N/A') }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $fee->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                        {{ ucfirst($fee->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="{{ route('admin.delivery-fees.edit', $fee) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.delivery-fees.destroy', $fee) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this configuration?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500 italic">{{ __('No delivery fee rules configured.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($fees->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $fees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
