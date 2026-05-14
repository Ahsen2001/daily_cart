<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Finance Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" class="grid gap-3 sm:grid-cols-4">
                    <x-text-input type="date" name="from" :value="request('from')" />
                    <x-text-input type="date" name="to" :value="request('to')" />
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    'total_revenue' => 'Total Revenue',
                    'total_delivery_charges' => 'Delivery Charges',
                    'total_service_charges' => 'Service Charges',
                    'total_vendor_payouts' => 'Vendor Payouts',
                    'total_rider_payouts' => 'Rider Payouts',
                    'total_refunds' => 'Refunds',
                    'total_cod_pending_payments' => 'COD Pending',
                ] as $key => $label)
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <div class="text-sm text-gray-500">{{ __($label) }}</div>
                        <div class="mt-1 text-2xl font-semibold">{{ \App\Services\CurrencyService::formatLkr($summary[$key]) }}</div>
                    </div>
                @endforeach
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">{{ __('Paid Orders') }}</div>
                    <div class="mt-1 text-2xl font-semibold">{{ $summary['total_paid_orders'] }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
