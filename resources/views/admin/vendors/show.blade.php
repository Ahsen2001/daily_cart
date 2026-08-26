<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-green-700">{{ __('Vendor details') }}</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $vendor->store_name }}</h2>
            </div>
            <a href="{{ route('admin.vendors.index') }}" class="text-sm font-semibold text-green-700 underline">{{ __('Back to vendors') }}</a>
        </div>
    </x-slot>

    @php
        $locationAddress = $vendor->latitude !== null && $vendor->longitude !== null && filled($vendor->formatted_address)
            ? $vendor->formatted_address
            : collect([$vendor->address, $vendor->city, $vendor->district, $vendor->province])->filter()->implode(', ');
        $storeProfile = $vendor->storeProfile;
    @endphp

    <div class="bg-[#F4FFF7] py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <section class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('Business account') }}</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $vendor->store_name }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $vendor->user?->name }}</p>
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-bold uppercase {{ $vendor->status === 'approved' ? 'bg-green-100 text-green-800' : ($vendor->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">
                            {{ str_replace('_', ' ', $vendor->status) }}
                        </span>
                    </div>

                    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-semibold text-gray-500">{{ __('Business registration number') }}</dt><dd class="mt-1 text-gray-900">{{ $vendor->business_registration_no ?: '—' }}</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Commission rate') }}</dt><dd class="mt-1 text-gray-900">{{ number_format((float) $vendor->commission_rate, 2) }}%</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Registered') }}</dt><dd class="mt-1 text-gray-900">{{ $vendor->created_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Approved') }}</dt><dd class="mt-1 text-gray-900">{{ $vendor->approved_at?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                    </dl>

                    <x-location-display
                        class="mt-6"
                        :label="__('Store location')"
                        :address="$locationAddress"
                        :latitude="$vendor->latitude"
                        :longitude="$vendor->longitude"
                        stacked
                    />
                </section>

                <aside class="space-y-6">
                    <section class="rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">{{ __('Contact and account') }}</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div><dt class="font-semibold text-gray-500">{{ __('Name') }}</dt><dd class="mt-1 break-words text-gray-900">{{ $vendor->user?->name ?: '—' }}</dd></div>
                            <div><dt class="font-semibold text-gray-500">{{ __('Email') }}</dt><dd class="mt-1 break-words text-gray-900">{{ $vendor->user?->email ?: '—' }}</dd></div>
                            <div><dt class="font-semibold text-gray-500">{{ __('Phone') }}</dt><dd class="mt-1 text-gray-900">{{ $vendor->phone ?: $vendor->user?->phone ?: '—' }}</dd></div>
                            <div><dt class="font-semibold text-gray-500">{{ __('Account status') }}</dt><dd class="mt-1 capitalize text-gray-900">{{ str_replace('_', ' ', $vendor->user?->status ?? '—') }}</dd></div>
                        </dl>
                    </section>

                    <section class="grid grid-cols-2 gap-4">
                        <div class="rounded-3xl bg-white p-5 text-center shadow-sm"><p class="text-2xl font-extrabold text-green-700">{{ number_format($vendor->products_count) }}</p><p class="mt-1 text-xs font-semibold uppercase text-gray-500">{{ __('Products') }}</p></div>
                        <div class="rounded-3xl bg-white p-5 text-center shadow-sm"><p class="text-2xl font-extrabold text-green-700">{{ number_format($vendor->orders_count) }}</p><p class="mt-1 text-xs font-semibold uppercase text-gray-500">{{ __('Orders') }}</p></div>
                    </section>
                </aside>
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="font-bold text-gray-900">{{ __('Storefront profile') }}</h3>
                    @if ($storeProfile)
                        <span class="text-xs font-bold uppercase text-gray-500">{{ str_replace('_', ' ', $storeProfile->profile_status) }}</span>
                    @endif
                </div>
                @if ($storeProfile)
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div><dt class="font-semibold text-gray-500">{{ __('Store URL slug') }}</dt><dd class="mt-1 break-words text-gray-900">{{ $storeProfile->slug }}</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Delivery estimate') }}</dt><dd class="mt-1 text-gray-900">{{ $storeProfile->delivery_estimate ?: '—' }}</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Minimum order') }}</dt><dd class="mt-1 text-gray-900">{{ $storeProfile->minimum_order !== null ? \App\Services\CurrencyService::formatLkr($storeProfile->minimum_order) : '—' }}</dd></div>
                        <div><dt class="font-semibold text-gray-500">{{ __('Visibility') }}</dt><dd class="mt-1 text-gray-900">{{ $storeProfile->is_enabled ? __('Enabled') : __('Disabled') }} · {{ $storeProfile->is_featured ? __('Featured') : __('Not featured') }}</dd></div>
                    </dl>
                    @if ($storeProfile->description)
                        <p class="mt-5 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $storeProfile->description }}</p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-gray-500">{{ __('This vendor has not created a storefront profile yet.') }}</p>
                @endif
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-3xl bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('Recent products') }}</h3>
                    <div class="mt-4 divide-y divide-gray-100 text-sm">
                        @forelse ($recentProducts as $product)
                            <div class="flex items-start justify-between gap-4 py-3"><div><p class="font-semibold text-gray-900">{{ $product->name }}</p><p class="text-xs text-gray-500">{{ $product->category?->name ?: __('Uncategorized') }}</p></div><span class="shrink-0 capitalize text-gray-600">{{ str_replace('_', ' ', $product->approval_status) }}</span></div>
                        @empty
                            <p class="py-3 text-gray-500">{{ __('No products yet.') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('Recent orders') }}</h3>
                    <div class="mt-4 divide-y divide-gray-100 text-sm">
                        @forelse ($recentOrders as $order)
                            <div class="flex items-start justify-between gap-4 py-3"><div><p class="font-semibold text-gray-900">{{ $order->order_number }}</p><p class="text-xs text-gray-500">{{ $order->customer?->user?->name ?: __('Customer') }}</p></div><div class="text-right"><p class="font-semibold text-gray-900">{{ \App\Services\CurrencyService::formatLkr($order->total_amount) }}</p><p class="text-xs capitalize text-gray-500">{{ str_replace('_', ' ', $order->order_status) }}</p></div></div>
                        @empty
                            <p class="py-3 text-gray-500">{{ __('No orders yet.') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
