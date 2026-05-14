<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Refund Management') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
                @endif

                <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['requested', 'approved', 'rejected', 'processed', 'failed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Filter') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead><tr class="text-left text-xs font-semibold uppercase text-gray-500"><th class="px-3 py-2">{{ __('Order') }}</th><th class="px-3 py-2">{{ __('Customer') }}</th><th class="px-3 py-2">{{ __('Vendor') }}</th><th class="px-3 py-2">{{ __('Amount') }}</th><th class="px-3 py-2">{{ __('Status') }}</th><th class="px-3 py-2">{{ __('Action') }}</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($refunds as $refund)
                                <tr>
                                    <td class="px-3 py-3">{{ $refund->order?->order_number }}</td>
                                    <td class="px-3 py-3">{{ $refund->order?->customer?->user?->name }}</td>
                                    <td class="px-3 py-3">{{ $refund->order?->vendor?->store_name }}</td>
                                    <td class="px-3 py-3">{{ \App\Services\CurrencyService::formatLkr($refund->amount) }}</td>
                                    <td class="px-3 py-3">{{ ucfirst($refund->status) }}</td>
                                    <td class="px-3 py-3">
                                        @if ($refund->status === 'requested')
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input name="admin_note" class="w-40 rounded-md border-gray-300 text-xs shadow-sm" placeholder="{{ __('Admin note') }}">
                                                    <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input name="admin_note" class="w-40 rounded-md border-gray-300 text-xs shadow-sm" placeholder="{{ __('Admin note') }}">
                                                    <x-danger-button>{{ __('Reject') }}</x-danger-button>
                                                </form>
                                            </div>
                                        @else
                                            {{ $refund->admin_note ?? '-' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No refunds found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $refunds->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
