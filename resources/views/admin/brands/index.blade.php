<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Brands') }}</h2>
            <a href="{{ route('admin.brands.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase text-white">{{ __('Create Brand') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
                    <x-text-input name="search" placeholder="Search brands" :value="request('search')" />
                    <select name="status" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($brands as $brand)
                            <tr>
                                <td class="py-3 font-semibold">{{ $brand->name }}</td>
                                <td class="py-3">{{ $brand->products_count }} {{ __('products') }}</td>
                                <td class="py-3">{{ ucfirst($brand->status) }}</td>
                                <td class="py-3 text-right">
                                    <a class="text-indigo-700 underline" href="{{ route('admin.brands.edit', $brand) }}">{{ __('Edit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-gray-500">{{ __('No brands found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-6">{{ $brands->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
