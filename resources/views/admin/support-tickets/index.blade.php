<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Support Ticket Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" class="mb-6 grid gap-3 md:grid-cols-4">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Subject') }}</th><th class="px-3 py-2">{{ __('User') }}</th><th class="px-3 py-2">{{ __('Priority') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($tickets as $ticket)
                                <tr><td class="px-3 py-3 font-medium">{{ $ticket->subject }}</td><td class="px-3 py-3">{{ $ticket->user?->name }}</td><td class="px-3 py-3">{{ ucfirst($ticket->priority) }}</td><td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</td><td class="px-3 py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('admin.support-tickets.show', $ticket) }}">{{ __('Manage') }}</a></td></tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No tickets found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $tickets->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
