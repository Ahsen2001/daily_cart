<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Delivery Monitoring') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" class="mb-6 grid gap-3 md:grid-cols-4">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['pending', 'assigned', 'picked_up', 'on_the_way', 'delivered', 'failed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <select name="rider_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All riders') }}</option>
                        @foreach ($riders as $rider)
                            <option value="{{ $rider->id }}" @selected((int) request('rider_id') === $rider->id)>{{ $rider->user?->name }}</option>
                        @endforeach
                    </select>
                    <x-text-input type="date" name="date" :value="request('date')" />
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Rider') }}</th><th class="px-3 py-2">{{ __('Scheduled') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Delivered') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($deliveries as $delivery)
                                <tr>
                                    <td class="px-3 py-3"><a class="text-indigo-700 underline" href="{{ route('admin.orders.show', $delivery->order) }}">{{ $delivery->order?->order_number }}</a></td>
                                    <td class="px-3 py-3">{{ $delivery->rider?->user?->name ?? __('Not assigned') }}</td>
                                    <td class="px-3 py-3">{{ $delivery->scheduled_at?->format('M d, Y h:i A') }}</td>
                                    <td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($delivery->status)) }}</td>
                                    <td class="px-3 py-3">{{ $delivery->delivered_at?->format('M d, Y h:i A') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No deliveries found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $deliveries->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
