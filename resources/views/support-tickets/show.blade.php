<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Support Ticket Details') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif
                <div class="font-semibold text-gray-900">{{ $ticket->subject }}</div>
                <div class="mt-2 text-sm text-gray-700">{{ $ticket->message }}</div>
                <div class="mt-3 text-xs text-gray-500">{{ ucfirst($ticket->priority) }} - {{ str_replace('_', ' ', ucfirst($ticket->status)) }}</div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 font-semibold text-gray-900">{{ __('Replies') }}</h3>
                <div class="space-y-4">
                    @foreach ($ticket->replies as $reply)
                        <div class="rounded-md border p-4 text-sm">
                            <div class="font-medium">{{ $reply->user?->name }}</div>
                            <div class="mt-1 text-gray-700">{{ $reply->message }}</div>
                            @if ($reply->attachment)
                                <a href="{{ Storage::url($reply->attachment) }}" class="mt-2 inline-block text-indigo-700 underline">{{ __('Attachment') }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('support.tickets.replies.store', $ticket) }}" enctype="multipart/form-data" class="mt-6 space-y-3">
                        @csrf
                        <textarea name="message" rows="4" class="w-full rounded-md border-gray-300 shadow-sm" required placeholder="{{ __('Reply message') }}"></textarea>
                        <input type="file" name="attachment" class="text-sm">
                        <x-primary-button>{{ __('Reply') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
