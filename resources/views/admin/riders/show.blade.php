<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-green-700">{{ __('Rider management') }}</p>
                <h2 class="text-xl font-semibold text-gray-800">{{ $rider->user?->name }}</h2>
            </div>
            <a href="{{ route('admin.riders.index') }}" class="text-sm font-semibold text-green-700 underline">{{ __('Back to riders') }}</a>
        </div>
    </x-slot>

    <div class="bg-[#F4FFF7] py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-5 rounded-2xl bg-white p-4 text-sm font-medium text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                <main class="space-y-6">
                    <section class="rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">{{ __('Rider profile') }}</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-bold uppercase text-gray-500">{{ __('Contact') }}</p>
                                <p class="mt-1 break-all">{{ $rider->user?->email }}</p>
                                <p>{{ $rider->user?->phone }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase text-gray-500">{{ __('Vehicle') }}</p>
                                <p class="mt-1">{{ ucfirst($rider->vehicle_type) }}{{ $rider->vehicle_number ? ' · '.$rider->vehicle_number : '' }}</p>
                                <p class="mt-2 text-xs font-bold uppercase text-gray-500">{{ __('Driving licence') }}</p>
                                <p class="mt-1 {{ $rider->license_number ? 'text-gray-900' : 'font-semibold text-red-700' }}">{{ $rider->license_number ?: __('Missing — add before verifying') }}</p>
                            </div>
                            <x-location-display
                                :label="__('Rider home base')"
                                :address="$rider->formatted_address ?: collect([$rider->address, $rider->city, $rider->district])->filter()->implode(', ')"
                                :latitude="$rider->latitude"
                                :longitude="$rider->longitude"
                                stacked
                            />
                        </div>
                    </section>

                    <section class="rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">{{ __('Recent delivery assignments') }}</h3>
                        <div class="mt-4 divide-y divide-gray-100">
                            @forelse ($rider->deliveries as $delivery)
                                <div class="flex flex-col gap-2 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-semibold">{{ $delivery->order?->order_number }}</p>
                                        <p class="text-gray-500">{{ $delivery->order?->customer?->user?->name }} · {{ $delivery->scheduled_at?->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold">{{ str_replace('_', ' ', ucfirst($delivery->status)) }}</span>
                                </div>
                            @empty
                                <p class="py-5 text-sm text-gray-500">{{ __('No delivery assignments yet.') }}</p>
                            @endforelse
                        </div>
                    </section>
                </main>

                <aside class="space-y-6">
                    <section class="rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">{{ __('Verification credentials') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Passwords are never displayed. Store only the licence number needed for operational verification.') }}</p>
                        <form method="POST" action="{{ route('admin.riders.credentials', $rider) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PATCH')
                            <label for="license_number" class="block text-sm font-semibold">{{ __('Driving licence number') }}</label>
                            <input id="license_number" name="license_number" value="{{ old('license_number', $rider->license_number) }}" required class="w-full rounded-xl border-gray-200" autocomplete="off">
                            <button class="w-full rounded-full border border-green-200 px-4 py-3 text-sm font-bold text-green-700">{{ __('Save verification credentials') }}</button>
                        </form>
                    </section>

                    <section class="rounded-3xl bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-gray-900">{{ __('Account control') }}</h3>
                        @if (blank($rider->license_number))
                            <p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm font-medium text-amber-800">{{ __('A driving licence number is required before this rider can be verified.') }}</p>
                        @endif
                        <form method="POST" action="{{ route('admin.riders.status', $rider) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PATCH')
                            <label class="block text-sm font-semibold">{{ __('Verification status') }}</label>
                            <select name="verification_status" class="w-full rounded-xl border-gray-200">
                                @foreach (['pending', 'verified', 'rejected', 'suspended'] as $status)
                                    <option value="{{ $status }}" @selected($rider->verification_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button class="w-full rounded-full bg-green-600 px-4 py-3 text-sm font-bold text-white">{{ __('Save account status') }}</button>
                        </form>

                        <form method="POST" action="{{ route('admin.riders.availability', $rider) }}" class="mt-6 space-y-3">
                            @csrf
                            @method('PATCH')
                            <label class="block text-sm font-semibold">{{ __('Operational availability') }}</label>
                            <select name="availability_status" class="w-full rounded-xl border-gray-200">
                                @foreach (['available', 'unavailable'] as $status)
                                    <option value="{{ $status }}" @selected($rider->availability_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <button class="w-full rounded-full border border-green-200 px-4 py-3 text-sm font-bold text-green-700">{{ __('Update availability') }}</button>
                        </form>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
