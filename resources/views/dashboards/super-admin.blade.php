<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Super Admin Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <div class="grid gap-4 text-sm text-gray-700 sm:grid-cols-3">
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.vendors.index') }}">{{ __('Review vendors') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.riders.index') }}">{{ __('Review riders') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.categories.index') }}">{{ __('Manage categories') }}</a>
                    <a class="font-medium text-indigo-700 underline" href="{{ route('admin.products.index') }}">{{ __('Review products') }}</a>
                    <span>{{ __('Full system access is enabled for this role.') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
