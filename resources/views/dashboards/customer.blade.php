<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Customer Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap gap-4">
                    <a class="font-medium text-indigo-700 underline" href="{{ route('customer.products.index') }}">{{ __('Browse approved products') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('customer.cart.index') }}">{{ __('View cart') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('customer.wishlist.index') }}">{{ __('View wishlist') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
