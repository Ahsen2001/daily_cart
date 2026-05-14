<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Product Reviews') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['visible', 'hidden', 'reported'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Product') }}</th><th class="px-3 py-2">{{ __('Customer') }}</th><th class="px-3 py-2">{{ __('Rating') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Comment') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($reviews as $review)
                                <tr><td class="px-3 py-3">{{ $review->product?->name }}</td><td class="px-3 py-3">{{ $review->customer?->user?->name }}</td><td class="px-3 py-3">{{ $review->rating }}/5</td><td class="px-3 py-3">{{ ucfirst($review->status) }}</td><td class="px-3 py-3">{{ $review->comment }}</td></tr>
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
