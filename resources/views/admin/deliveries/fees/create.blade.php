<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Add Delivery Fee Rule') }}</h2>
            <a href="{{ route('admin.delivery-fees.index') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.delivery-fees.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="district" :value="__('District Name')" />
                        <x-text-input id="district" name="district" type="text" class="mt-1 block w-full" :value="old('district')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('district')" />
                    </div>

                    <div>
                        <x-input-label for="base_fee" :value="__('Base Fee (LKR)')" />
                        <x-text-input id="base_fee" name="base_fee" type="number" step="0.01" class="mt-1 block w-full" :value="old('base_fee', '0.00')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('base_fee')" />
                    </div>

                    <div>
                        <x-input-label for="per_km_fee" :value="__('Per KM Charge (LKR)')" />
                        <x-text-input id="per_km_fee" name="per_km_fee" type="number" step="0.01" class="mt-1 block w-full" :value="old('per_km_fee', '0.00')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('per_km_fee')" />
                    </div>

                    <div>
                        <x-input-label for="minimum_order" :value="__('Minimum Order Limit (LKR)')" />
                        <x-text-input id="minimum_order" name="minimum_order" type="number" step="0.01" class="mt-1 block w-full" :value="old('minimum_order', '0.00')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('minimum_order')" />
                    </div>

                    <div>
                        <x-input-label for="free_delivery_limit" :value="__('Free Delivery Threshold (LKR) - Leave empty to disable')" />
                        <x-text-input id="free_delivery_limit" name="free_delivery_limit" type="number" step="0.01" class="mt-1 block w-full" :value="old('free_delivery_limit')" />
                        <x-input-error class="mt-2" :messages="$errors->get('free_delivery_limit')" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active" @selected(old('status') === 'active')>{{ __('Active') }}</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>{{ __('Inactive') }}</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>
                            {{ __('Save Configuration') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
