<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Rider Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg">
                {{ __('View assigned deliveries and update your delivery statuses here.') }}
            </div>
        </div>
    </div>
</x-app-layout>
