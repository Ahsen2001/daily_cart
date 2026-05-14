<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Rider Approval Pending') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg">
                {{ __('Your rider account is waiting for verification. You will be able to access the rider dashboard after approval.') }}
            </div>
        </div>
    </div>
</x-app-layout>
