<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $customer->user?->name }}</h2>
            <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-[1fr_340px] lg:px-8">
            <div class="space-y-6">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('Recent Orders') }}</h3>
                    <div class="mt-4 divide-y text-sm">
                        @forelse ($customer->orders as $order)
                            <div class="flex justify-between py-3">
                                <span>{{ $order->order_number }}</span>
                                <span>{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500">{{ __('No orders yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ __('Addresses') }}</h3>
                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        @forelse ($customer->addresses as $address)
                            <div class="rounded-lg border border-gray-100 p-4">
                                <p class="font-semibold">{{ $address->label }}</p>
                                <p class="text-gray-600">{{ $address->address_line_1 }}</p>
                                <p class="text-gray-500">{{ $address->city }}, {{ $address->district }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">{{ __('No addresses saved.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                    @endif
                    <h3 class="font-bold text-gray-900">{{ __('Account') }}</h3>
                    <div class="mt-4 space-y-2 text-sm text-gray-600">
                        <p>{{ $customer->user?->email }}</p>
                        <p>{{ $customer->phone ?: $customer->user?->phone }}</p>
                        <p>{{ __('Wallet') }}: {{ \App\Services\CurrencyService::formatLkr($customer->wallet_balance) }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.customers.status', $customer) }}" class="mt-5 space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['active', 'inactive', 'suspended'] as $status)
                                <option value="{{ $status }}" @selected($customer->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>{{ __('Update Status') }}</x-primary-button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
