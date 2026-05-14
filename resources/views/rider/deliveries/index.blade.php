<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Assigned Deliveries') }}</h2>
            <a href="{{ route('rider.deliveries.earnings') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Earnings') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['assigned', 'picked_up', 'on_the_way', 'delivered', 'failed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Customer') }}</th><th class="px-3 py-2">{{ __('Vendor') }}</th><th class="px-3 py-2">{{ __('Scheduled') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($deliveries as $delivery)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $delivery->order?->order_number }}</td>
                                    <td class="px-3 py-3">{{ $delivery->order?->customer?->user?->name }}</td>
                                    <td class="px-3 py-3">{{ $delivery->order?->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ $delivery->scheduled_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($delivery->status)) }}</td>
                                    <td class="px-3 py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('rider.deliveries.show', $delivery) }}">{{ __('Update') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No assigned deliveries found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $deliveries->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
