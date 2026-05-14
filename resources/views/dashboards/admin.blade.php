<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Admin Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-4 text-sm text-gray-700 sm:grid-cols-2">
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.vendors.index') }}">{{ __('Manage vendor approvals') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.riders.index') }}">{{ __('Manage rider approvals') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
