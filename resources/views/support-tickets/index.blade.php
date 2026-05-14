<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Support Tickets') }}</h2>
            <a href="{{ route('support.tickets.create') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Create ticket') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Subject') }}</th><th class="px-3 py-2">{{ __('Priority') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Created') }}</th><th class="px-3 py-2"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($tickets as $ticket)
                                <tr><td class="px-3 py-3 font-medium">{{ $ticket->subject }}</td><td class="px-3 py-3">{{ ucfirst($ticket->priority) }}</td><td class="px-3 py-3">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</td><td class="px-3 py-3">{{ $ticket->created_at->format('M d, Y') }}</td><td class="px-3 py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('support.tickets.show', $ticket) }}">{{ __('View') }}</a></td></tr>
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
