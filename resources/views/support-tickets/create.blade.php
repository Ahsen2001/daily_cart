<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Create Support Ticket') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('support.tickets.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="order_id" :value="__('Order')" />
                        <select id="order_id" name="order_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">{{ __('No linked order') }}</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="subject" :value="__('Subject')" />
                        <x-text-input id="subject" name="subject" class="mt-1 w-full" required />
                    </div>
                    <div>
                        <x-input-label for="priority" :value="__('Priority')" />
                        <select id="priority" name="priority" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="message" :value="__('Message')" />
                        <textarea id="message" name="message" rows="6" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>{{ old('message') }}</textarea>
                    </div>
                    <x-primary-button>{{ __('Create Ticket') }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
