<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Manage Support Ticket') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <div class="font-semibold text-gray-900">{{ $ticket->subject }}</div>
                    <div class="mt-2 text-sm text-gray-700">{{ $ticket->message }}</div>
                    <div class="mt-3 text-xs text-gray-500">{{ $ticket->user?->name }} - {{ ucfirst($ticket->priority) }} - {{ str_replace('_', ' ', ucfirst($ticket->status)) }}</div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 font-semibold">{{ __('Replies') }}</h3>
                    <div class="space-y-4">
                        @foreach ($ticket->replies as $reply)
                            <div class="rounded-md border p-4 text-sm">
                                <div class="font-medium">{{ $reply->user?->name }}</div>
                                <div class="mt-1">{{ $reply->message }}</div>
                            </div>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('admin.support-tickets.replies.store', $ticket) }}" enctype="multipart/form-data" class="mt-6 space-y-3">
                        @csrf
                        <textarea name="message" rows="4" class="w-full rounded-md border-gray-300 shadow-sm" required placeholder="{{ __('Reply message') }}"></textarea>
                        <input type="file" name="attachment" class="text-sm">
                        <x-primary-button>{{ __('Reply') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 font-semibold">{{ __('Ticket Controls') }}</h3>
                <form method="POST" action="{{ route('admin.support-tickets.update', $ticket) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="assigned_admin_id" :value="__('Assigned Admin')" />
                        <select id="assigned_admin_id" name="assigned_admin_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">{{ __('No change') }}</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected($ticket->assigned_admin_id === $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                                <option value="{{ $status }}" @selected($ticket->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Update Ticket') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
