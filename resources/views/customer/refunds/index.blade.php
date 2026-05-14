<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('My Refunds') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Amount') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Reason') }}</th><th class="px-3 py-2">{{ __('Requested') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($refunds as $order)
                                @foreach ($order->refunds as $refund)
                                    <tr><td class="px-3 py-3">{{ $order->order_number }}</td><td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($refund->amount) }}</td><td class="px-3 py-3">{{ ucfirst($refund->status) }}</td><td class="px-3 py-3">{{ $refund->reason }}</td><td class="px-3 py-3">{{ $refund->requested_at?->format('M d, Y') }}</td></tr>
                                @endforeach
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No refund requests found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $refunds->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
