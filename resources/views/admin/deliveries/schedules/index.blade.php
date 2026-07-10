<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Customer Delivery Scheduling') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back to Dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold">
                            <th class="p-4">{{ __('Order Number') }}</th>
                            <th class="p-4">{{ __('Customer') }}</th>
                            <th class="p-4">{{ __('Scheduled Date') }}</th>
                            <th class="p-4">{{ __('Time Slot Window') }}</th>
                            <th class="p-4">{{ __('Scheduling Status') }}</th>
                            <th class="p-4 text-right">{{ __('Update Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse ($schedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-semibold text-gray-900">{{ $schedule->order?->order_number ?? __('N/A') }}</td>
                                <td class="p-4">{{ $schedule->order?->customer?->user?->name ?? __('N/A') }}</td>
                                <form method="POST" action="{{ route('admin.delivery-schedules.update', $schedule) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <td class="p-4">
                                        <input type="date" name="scheduled_date" value="{{ $schedule->scheduled_date?->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>
                                    <td class="p-4">
                                        <input type="text" name="delivery_window" value="{{ $schedule->delivery_window }}" class="rounded-md border-gray-300 shadow-sm text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500 w-44" placeholder="10:00 AM - 12:00 PM">
                                        <input type="hidden" name="scheduled_time" value="{{ $schedule->scheduled_time }}">
                                    </td>
                                    <td class="p-4">
                                        <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="pending" @selected($schedule->status === 'pending')>{{ __('Pending') }}</option>
                                            <option value="scheduled" @selected($schedule->status === 'scheduled')>{{ __('Scheduled') }}</option>
                                            <option value="dispatched" @selected($schedule->status === 'dispatched')>{{ __('Dispatched') }}</option>
                                            <option value="delivered" @selected($schedule->status === 'delivered')>{{ __('Delivered') }}</option>
                                            <option value="cancelled" @selected($schedule->status === 'cancelled')>{{ __('Cancelled') }}</option>
                                        </select>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button type="submit" class="rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700">
                                            {{ __('Update') }}
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500 italic">{{ __('No delivery schedules mapped.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($schedules->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $schedules->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
