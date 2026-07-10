<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold">{{ $message->subject }}</h2><a class="text-sm font-medium text-indigo-700 underline" href="{{ route('admin.contact-messages.index') }}">{{ __('Back') }}</a></div></x-slot>
    <div class="py-12"><div class="mx-auto max-w-4xl sm:px-6 lg:px-8"><div class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
        @if (session('status'))<div class="text-sm font-medium text-green-700">{{ session('status') }}</div>@endif
        <div class="grid gap-4 text-sm sm:grid-cols-3"><p><strong>{{ __('Name') }}:</strong> {{ $message->name }}</p><p><strong>{{ __('Email') }}:</strong> {{ $message->email }}</p><p><strong>{{ __('Phone') }}:</strong> {{ $message->phone ?: '-' }}</p></div>
        <div class="rounded-lg bg-gray-50 p-4 text-sm leading-7 text-gray-700">{{ $message->message }}</div>
        <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="flex gap-3">@csrf @method('PATCH')<select name="status" class="border-gray-300 rounded-md shadow-sm">@foreach (['pending','read','replied','closed'] as $status)<option value="{{ $status }}" @selected($message->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><x-primary-button>{{ __('Update') }}</x-primary-button></form>
    </div></div></div>
</x-app-layout>
