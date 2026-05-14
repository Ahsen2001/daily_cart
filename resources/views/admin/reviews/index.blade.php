<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Review Moderation') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-6 md:grid-cols-4">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Average Rating') }}</div><div class="mt-1 text-2xl font-semibold">{{ $analytics['average_rating'] }}</div></div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Reviews') }}</div><div class="mt-1 text-2xl font-semibold">{{ $analytics['review_count'] }}</div></div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Reported') }}</div><div class="mt-1 text-2xl font-semibold">{{ $analytics['reported_count'] }}</div></div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg"><div class="text-sm text-gray-500">{{ __('Hidden') }}</div><div class="mt-1 text-2xl font-semibold">{{ $analytics['hidden_count'] }}</div></div>
            </div>
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Product') }}</th><th class="px-3 py-2">{{ __('Vendor') }}</th><th class="px-3 py-2">{{ __('Rating') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Actions') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($reviews as $review)
                                <tr>
                                    <td class="px-3 py-3">{{ $review->product?->name }}</td>
                                    <td class="px-3 py-3">{{ $review->product?->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ $review->rating }}/5</td>
                                    <td class="px-3 py-3">{{ ucfirst($review->status) }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.reviews.hide', $review) }}">@csrf @method('PATCH')<x-secondary-button>{{ __('Hide') }}</x-secondary-button></form>
                                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">@csrf @method('DELETE')<x-danger-button>{{ __('Delete') }}</x-danger-button></form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No reviews found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $reviews->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
