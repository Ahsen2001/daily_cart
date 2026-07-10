<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ __('Contact Messages') }}</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-7xl sm:px-6 lg:px-8"><div class="bg-white p-6 shadow-sm sm:rounded-lg">
        <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
            <x-text-input name="search" placeholder="Search messages" :value="request('search')" />
            <select name="status" class="border-gray-300 rounded-md shadow-sm">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (['pending','read','replied','closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-primary-button>{{ __('Filter') }}</x-primary-button>
        </form>
        <table class="min-w-full divide-y divide-gray-200 text-sm"><tbody class="divide-y divide-gray-100">
            @forelse ($messages as $message)
                <tr><td class="py-3"><p class="font-semibold">{{ $message->subject }}</p><p class="text-xs text-gray-500">{{ $message->name }} · {{ $message->email }}</p></td><td class="py-3">{{ ucfirst($message->status) }}</td><td class="py-3 text-right"><a class="text-indigo-700 underline" href="{{ route('admin.contact-messages.show', $message) }}">{{ __('Open') }}</a></td></tr>
            @empty
                <tr><td class="py-6 text-center text-gray-500">{{ __('No messages found.') }}</td></tr>
            @endforelse
        </tbody></table>
        <div class="mt-6">{{ $messages->links() }}</div>
    </div></div></div>
</x-app-layout>
