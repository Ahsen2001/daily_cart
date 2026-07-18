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
                <p class="mt-2 leading-6">{{ __('Active district rules apply to web checkout, API quotes, order creation, payments, and recurring orders. Base Fee is always charged; Per KM Fee is added when distance is available. Minimum Order blocks smaller vendor orders, while Free Delivery Limit makes eligible delivery free.') }}</p>
                <p class="mt-2 leading-6">{{ __('Use “All Districts”, “Default”, or “*” as an active fallback rule for locations without an exact district match. Only Admin and Super Admin accounts can manage these configurations.') }}</p>
            </div>

            <section class="mb-6 rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ __('Service Charge Configuration') }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Applied to each vendor subtotal at checkout and retained with the created order and payment.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.delivery-fees.service-charge.update') }}" class="flex w-full max-w-sm items-end gap-3 sm:w-auto">
                        @csrf
                        @method('PUT')
                        <div class="min-w-0 flex-1">
                            <x-input-label for="service_charge_rate_percent" :value="__('Service Charge (%)')" />
                            <x-text-input id="service_charge_rate_percent" name="service_charge_rate_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('service_charge_rate_percent', number_format($serviceChargeRatePercent, 2, '.', ''))" required />
                            <x-input-error :messages="$errors->get('service_charge_rate_percent')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </form>
                </div>
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
