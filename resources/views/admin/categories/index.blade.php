<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Categories') }}</h2>
            <a href="{{ route('admin.categories.create') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Create Category') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="grid gap-3 mb-6 sm:grid-cols-3">
                    <x-text-input name="search" placeholder="Search categories" :value="request('search')" />
                    <select name="status" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="space-y-3">
                    @forelse ($categories as $category)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                <div class="text-sm text-gray-600">{{ $category->products_count }} products · {{ ucfirst($category->status) }}</div>
                            </div>
                            <div class="flex gap-3">
                                <a class="text-indigo-700 underline" href="{{ route('admin.categories.edit', $category) }}">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-700 underline">{{ __('Deactivate') }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('No categories found.') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">{{ $categories->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
