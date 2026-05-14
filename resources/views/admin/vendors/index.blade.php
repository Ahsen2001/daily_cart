<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Vendor Approvals') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-700">{{ session('status') }}</div>
                    @endif

                    <div class="space-y-4">
                        @forelse ($vendors as $vendor)
                            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $vendor->store_name }}</div>
                                    <div class="text-sm text-gray-600">{{ $vendor->user?->email }} · {{ ucfirst($vendor->status) }}</div>
                                </div>

                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-primary-button>{{ __('Approve') }}</x-primary-button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-secondary-button>{{ __('Reject') }}</x-secondary-button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600">{{ __('No vendors found.') }}</p>
                        @endforelse
                    </div>

                    <div class="mt-6">{{ $vendors->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
