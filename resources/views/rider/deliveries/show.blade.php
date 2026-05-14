<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Delivery Update') }}</h2>
            <a href="{{ route('rider.deliveries.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><div class="text-sm text-gray-500">{{ __('Order') }}</div><div class="font-semibold">{{ $delivery->order?->order_number }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Status') }}</div><div class="font-semibold">{{ str_replace('_', ' ', ucfirst($delivery->status)) }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Customer') }}</div><div class="font-semibold">{{ $delivery->order?->customer?->user?->name }}</div></div>
                    <div><div class="text-sm text-gray-500">{{ __('Scheduled Delivery') }}</div><div class="font-semibold">{{ $delivery->scheduled_at?->format('M d, Y h:i A') }}</div></div>
                    <div class="sm:col-span-2"><div class="text-sm text-gray-500">{{ __('Delivery Address') }}</div><div class="font-semibold">{{ $delivery->delivery_address }}</div></div>
                    <div class="sm:col-span-2"><div class="text-sm text-gray-500">{{ __('Pickup Address') }}</div><div class="font-semibold">{{ $delivery->pickup_address }}</div></div>
                </div>

                @if ($delivery->proofs->isNotEmpty())
                    <div class="mt-6 border-t pt-6">
                        <h3 class="mb-3 font-semibold">{{ __('Delivery Proofs') }}</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($delivery->proofs as $proof)
                                <div class="rounded-md border p-3 text-sm">
                                    <img src="{{ Storage::url($proof->proof_image) }}" alt="Delivery proof" class="mb-3 h-40 w-full rounded object-cover">
                                    <div>{{ $proof->submitted_at?->format('M d, Y h:i A') }}</div>
                                    @if ($proof->note)
                                        <div class="mt-1 text-gray-500">{{ $proof->note }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold">{{ __('Status Actions') }}</h3>
                    <div class="space-y-3">
                        @if ($delivery->status === 'assigned')
                            <form method="POST" action="{{ route('rider.deliveries.accept', $delivery) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="w-full justify-center">{{ __('Accept Delivery') }}</x-primary-button>
                            </form>
                            <form method="POST" action="{{ route('rider.deliveries.picked-up', $delivery) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="w-full justify-center">{{ __('Mark Picked Up') }}</x-primary-button>
                            </form>
                        @endif

                        @if ($delivery->status === 'picked_up')
                            <form method="POST" action="{{ route('rider.deliveries.on-the-way', $delivery) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="w-full justify-center">{{ __('Mark On The Way') }}</x-primary-button>
                            </form>
                        @endif
                    </div>
                </div>

                @if ($delivery->status === 'on_the_way')
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="mb-3 font-semibold">{{ __('Complete Delivery') }}</h3>
                        <form method="POST" action="{{ route('rider.deliveries.delivered', $delivery) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="proof_image" :value="__('Proof Photo')" />
                                <input id="proof_image" type="file" name="proof_image" accept="image/*" class="mt-1 w-full text-sm" required>
                            </div>
                            <div>
                                <x-input-label for="customer_signature" :value="__('Customer Signature')" />
                                <input id="customer_signature" type="file" name="customer_signature" accept="image/*" class="mt-1 w-full text-sm">
                            </div>
                            <textarea name="note" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="{{ __('Optional note') }}"></textarea>
                            <x-primary-button class="w-full justify-center">{{ __('Mark Delivered') }}</x-primary-button>
                        </form>
                    </div>
                @endif

                @if (in_array($delivery->status, ['assigned', 'picked_up', 'on_the_way'], true))
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="mb-3 font-semibold">{{ __('Failed Delivery') }}</h3>
                        <form method="POST" action="{{ route('rider.deliveries.failed', $delivery) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <textarea name="failed_reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" required placeholder="{{ __('Failed delivery reason') }}"></textarea>
                            <x-danger-button class="w-full justify-center">{{ __('Mark Failed') }}</x-danger-button>
                        </form>
                    </div>
                @endif

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-3 font-semibold">{{ __('Update Location') }}</h3>
                    <form method="POST" action="{{ route('rider.location.store') }}" class="space-y-3">
                        @csrf
                        <x-text-input name="latitude" type="number" step="0.000001" placeholder="Latitude" class="w-full" required />
                        <x-text-input name="longitude" type="number" step="0.000001" placeholder="Longitude" class="w-full" required />
                        <x-primary-button class="w-full justify-center">{{ __('Save Location') }}</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
