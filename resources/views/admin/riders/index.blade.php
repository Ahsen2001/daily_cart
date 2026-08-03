<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-green-700">{{ __('Operations') }}</p>
                <h2 class="text-xl font-semibold text-gray-800">{{ __('Rider Management') }}</h2>
            </div>
            <p class="text-sm text-gray-500">{{ __('Approve riders, monitor availability, and review active work.') }}</p>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-5 rounded-2xl bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-700 shadow-sm">{{ $errors->first() }}</div>
            @endif

            <form method="GET" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input name="search" value="{{ request('search') }}" placeholder="{{ __('Search name, email or phone') }}" class="rounded-xl border-gray-200 text-sm">
                <select name="verification_status" class="rounded-xl border-gray-200 text-sm">
                    <option value="">{{ __('All account states') }}</option>
                    @foreach (['pending', 'verified', 'rejected', 'suspended'] as $status)
                        <option value="{{ $status }}" @selected(request('verification_status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="availability_status" class="rounded-xl border-gray-200 text-sm">
                    <option value="">{{ __('All availability') }}</option>
                    @foreach (['available', 'unavailable', 'delivering'] as $status)
                        <option value="{{ $status }}" @selected(request('availability_status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button class="rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white">{{ __('Filter riders') }}</button>
            </form>

            <div class="space-y-4">
                @forelse ($riders as $rider)
                    <article class="rounded-3xl bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-center">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-gray-900">{{ $rider->user?->name }}</h3>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ ucfirst($rider->verification_status) }}</span>
                                    <span class="rounded-full {{ $rider->availability_status === 'available' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }} px-2.5 py-1 text-xs font-bold">{{ str_replace('_', ' ', ucfirst($rider->availability_status)) }}</span>
                                    <span class="rounded-full {{ $rider->license_number ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700' }} px-2.5 py-1 text-xs font-bold">{{ $rider->license_number ? __('Licence on file') : __('Licence missing') }}</span>
                                </div>
                                <p class="mt-1 break-all text-sm text-gray-600">{{ $rider->user?->email }} · {{ $rider->user?->phone }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ ucfirst($rider->vehicle_type) }}{{ $rider->vehicle_number ? ' · '.$rider->vehicle_number : '' }}</p>
                                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                                    <div class="rounded-xl bg-gray-50 p-3"><span class="block text-xs text-gray-500">{{ __('Active deliveries') }}</span><strong>{{ $rider->active_deliveries_count }}</strong></div>
                                    <div class="rounded-xl bg-gray-50 p-3"><span class="block text-xs text-gray-500">{{ __('Completed') }}</span><strong>{{ $rider->completed_deliveries_count }}</strong></div>
                                    <x-location-display class="min-w-0" :label="__('Home base')" :address="$rider->formatted_address ?: collect([$rider->address, $rider->city, $rider->district])->filter()->implode(', ')" :latitude="$rider->latitude" :longitude="$rider->longitude" compact stacked />
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 xl:w-72 xl:justify-end">
                                <a href="{{ route('admin.riders.show', $rider) }}" class="rounded-full border border-green-200 px-4 py-2 text-sm font-bold text-green-700">{{ __('Manage') }}</a>
                                @if ($rider->verification_status !== 'verified')
                                    <form method="POST" action="{{ route('admin.riders.approve', $rider) }}">@csrf @method('PATCH')<button class="rounded-full bg-green-600 px-4 py-2 text-sm font-bold text-white">{{ __('Approve') }}</button></form>
                                @endif
                                @if (!in_array($rider->verification_status, ['rejected', 'suspended'], true))
                                    <form method="POST" action="{{ route('admin.riders.reject', $rider) }}">@csrf @method('PATCH')<button class="rounded-full border border-red-200 px-4 py-2 text-sm font-bold text-red-700">{{ __('Reject') }}</button></form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl bg-white p-10 text-center text-gray-500 shadow-sm">{{ __('No riders match the selected filters.') }}</div>
                @endforelse
            </div>
            <div class="mt-6">{{ $riders->links() }}</div>
        </div>
    </div>
</x-app-layout>
