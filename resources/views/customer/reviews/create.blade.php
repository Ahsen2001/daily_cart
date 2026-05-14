<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Write Review') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif
                <div class="mb-6">
                    <div class="text-sm text-gray-500">{{ __('Product') }}</div>
                    <div class="font-semibold text-gray-900">{{ $product->name }}</div>
                </div>
                <form method="POST" action="{{ route('customer.reviews.store', [$order, $product]) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="rating" :value="__('Rating')" />
                        <select id="rating" name="rating" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}">{{ $rating }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <x-input-label for="comment" :value="__('Comment')" />
                        <textarea id="comment" name="comment" rows="5" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('comment') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="image" :value="__('Review Image')" />
                        <input id="image" type="file" name="image" accept="image/*" class="mt-1 text-sm">
                    </div>
                    <x-primary-button>{{ __('Submit Review') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
