<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Customer Management') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif

                <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
                    <x-text-input name="search" placeholder="Search name, email, phone" :value="request('search')" />
                    <select name="status" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['active', 'inactive', 'suspended'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase text-gray-500">
                                <th class="px-3 py-2">{{ __('Customer') }}</th>
                                <th class="px-3 py-2">{{ __('Orders') }}</th>
                                <th class="px-3 py-2">{{ __('Wishlist') }}</th>
                                <th class="px-3 py-2">{{ __('Tickets') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="px-3 py-3">
                                        <p class="font-semibold text-gray-900">{{ $customer->user?->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $customer->user?->email }}</p>
                                    </td>
                                    <td class="px-3 py-3">{{ $customer->orders_count }}</td>
                                    <td class="px-3 py-3">{{ $customer->wishlists_count }}</td>
                                    <td class="px-3 py-3">{{ $customer->support_tickets_count }}</td>
                                    <td class="px-3 py-3">{{ ucfirst($customer->status) }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <a class="text-indigo-700 underline" href="{{ route('admin.customers.show', $customer) }}">{{ __('View') }}</a>
                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="ml-3 inline" onsubmit="return confirm('{{ __('Delete this customer account permanently from active DailyCart access?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-700 underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No customers found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $customers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
