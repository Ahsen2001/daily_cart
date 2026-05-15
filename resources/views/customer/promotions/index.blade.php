<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Active Promotions') }}</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8"><div class="grid gap-4 md:grid-cols-2">
        @forelse ($promotions as $promotion)
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($promotion->banner_image)<img src="{{ Storage::url($promotion->banner_image) }}" alt="{{ $promotion->title }}" class="mb-4 h-44 w-full rounded object-cover">@endif
                <div class="text-lg font-semibold">{{ $promotion->title }}</div>
                <div class="mt-1 text-sm text-gray-500">{{ $promotion->description }}</div>
                <div class="mt-3 text-sm">{{ str_replace('_', ' ', ucfirst($promotion->promotion_type)) }} - {{ str_replace('_', ' ', ucfirst($promotion->discount_type)) }} {{ $promotion->discount_value }}</div>
            </div>
        @empty
            <div class="bg-white p-6 text-sm text-gray-500 shadow-sm sm:rounded-lg">{{ __('No active promotions found.') }}</div>
        @endforelse
    </div></div></div>
</x-app-layout>
