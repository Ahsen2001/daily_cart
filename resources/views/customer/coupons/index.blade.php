<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Available Coupons') }}</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8"><div class="grid gap-4 md:grid-cols-2">
        @forelse ($coupons as $coupon)
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="text-lg font-semibold">{{ $coupon->title ?? $coupon->code }}</div>
                <div class="mt-1 text-sm text-gray-500">{{ $coupon->description }}</div>
                <div class="mt-3 text-sm"><span class="font-semibold">{{ __('Code') }}:</span> {{ $coupon->code }}</div>
                <div class="text-sm"><span class="font-semibold">{{ __('Minimum Order') }}:</span> {{ \App\Services\CurrencyService::formatLkr($coupon->minimum_order_amount) }}</div>
                <div class="text-sm"><span class="font-semibold">{{ __('Expires') }}:</span> {{ $coupon->expires_at?->format('M d, Y h:i A') }}</div>
            </div>
        @empty
            <div class="bg-white p-6 text-sm text-gray-500 shadow-sm sm:rounded-lg">{{ __('No active coupons found.') }}</div>
        @endforelse
    </div><div class="mt-6">{{ $coupons->links() }}</div></div></div>
</x-app-layout>
