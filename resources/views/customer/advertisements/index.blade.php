<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Advertisements') }}</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8"><div class="grid gap-4 md:grid-cols-2">
        @forelse ($advertisements as $advertisement)
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <img src="{{ Storage::url($advertisement->image_path) }}" alt="{{ $advertisement->title }}" class="mb-4 h-44 w-full rounded object-cover">
                <div class="text-lg font-semibold">{{ $advertisement->title }}</div>
                <div class="mt-2 text-sm text-gray-500">{{ str_replace('_', ' ', ucfirst($advertisement->position ?? $advertisement->placement)) }}</div>
            </div>
        @empty
            <div class="bg-white p-6 text-sm text-gray-500 shadow-sm sm:rounded-lg">{{ __('No active advertisements found.') }}</div>
        @endforelse
    </div></div></div>
</x-app-layout>
