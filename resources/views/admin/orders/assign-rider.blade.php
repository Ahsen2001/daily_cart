<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Assign Rider') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="mb-6 space-y-2 text-sm">
                    <div><span class="font-semibold">{{ __('Order') }}:</span> {{ $order->order_number }}</div>
                    <div><span class="font-semibold">{{ __('Vendor') }}:</span> {{ $order->vendor?->store_name }}</div>
                    <div><span class="font-semibold">{{ __('Customer') }}:</span> {{ $order->customer?->user?->name }}</div>
                    <div><span class="font-semibold">{{ __('Scheduled Delivery') }}:</span> {{ $order->scheduled_delivery_at?->format('M d, Y h:i A') }}</div>
                </div>

                <form method="POST" action="{{ route('admin.orders.assign-rider.store', $order) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="rider_id" :value="__('Rider')" />
                        <select id="rider_id" name="rider_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">{{ __('Select rider') }}</option>
                            @foreach ($riders as $rider)
                                <option value="{{ $rider->id }}" @selected(old('rider_id', $order->delivery?->rider_id) == $rider->id)>
                                    {{ $rider->user?->name }} - {{ str_replace('_', ' ', ucfirst($rider->availability_status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Assign Rider') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
