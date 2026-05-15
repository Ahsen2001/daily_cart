<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">{{ __('Support Ticket Report') }}</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="flex flex-wrap gap-2">
                <a class="rounded bg-gray-800 px-3 py-2 text-sm text-white" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">{{ __('Export CSV') }}</a>
                <a class="rounded bg-gray-600 px-3 py-2 text-sm text-white" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">{{ __('Excel placeholder') }}</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($summary as $key => $value)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase text-gray-500">{{ __(str_replace('_', ' ', $key)) }}</p>
                        <p class="mt-2 text-xl font-bold">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-4 py-3">{{ __('Subject') }}</th><th>{{ __('User') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th><th>{{ __('Assigned Admin') }}</th><th>{{ __('Created') }}</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($tickets as $ticket)
                            <tr><td class="px-4 py-3">{{ $ticket->subject }}</td><td>{{ $ticket->user?->name }}</td><td>{{ $ticket->priority }}</td><td>{{ $ticket->status }}</td><td>{{ $ticket->assignedAdmin?->name }}</td><td>{{ $ticket->created_at?->format('Y-m-d H:i') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $tickets->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
