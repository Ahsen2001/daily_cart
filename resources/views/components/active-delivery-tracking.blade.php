@props(['order'])

@php
    $tracking = $order->delivery?->trackingState;
    $locationAvailable = $tracking?->recorded_at && $tracking?->latitude !== null && $tracking?->longitude !== null;
    $trackingId = 'active-delivery-tracking-'.$order->id;
@endphp

@if ($order->delivery)
    <section id="{{ $trackingId }}" class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">{{ __('Active delivery tracking') }}</p>
                <p data-tracking-status class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $tracking?->status === 'active' ? __('Receiving rider location') : __('Tracking is not active') }}
                </p>
            </div>
            <span data-tracking-indicator class="h-3 w-3 rounded-full {{ $tracking?->status === 'active' ? 'animate-pulse bg-emerald-500' : 'bg-gray-300' }}"></span>
        </div>
        <div data-tracking-location class="mt-3 {{ $locationAvailable ? '' : 'hidden' }} text-sm text-gray-700">
            <p><span class="font-semibold">{{ __('Coordinates') }}:</span> <span data-tracking-coordinates>{{ $locationAvailable ? number_format((float) $tracking->latitude, 6).', '.number_format((float) $tracking->longitude, 6) : '' }}</span></p>
            <p class="mt-1"><span class="font-semibold">{{ __('Last update') }}:</span> <span data-tracking-time>{{ $tracking?->recorded_at?->format('M d, Y h:i:s A') }}</span></p>
            <a data-tracking-map target="_blank" rel="noopener noreferrer"
               href="{{ $locationAvailable ? 'https://www.google.com/maps?q='.$tracking->latitude.','.$tracking->longitude : '#' }}"
               class="mt-3 inline-flex rounded-full bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800">
                {{ __('Open rider location') }}
            </a>
        </div>
        <p data-tracking-empty class="mt-3 text-sm text-gray-500 {{ $locationAvailable ? 'hidden' : '' }}">{{ __('Waiting for the rider’s first GPS update.') }}</p>
    </section>

        <script>
            (() => {
                const root = document.getElementById(@json($trackingId));
                if (!root || !window.EventSource) return;
                const stream = new EventSource(@json(route('orders.tracking.stream', $order)));
                stream.addEventListener('location', event => {
                    const payload = JSON.parse(event.data);
                    const active = payload.tracking?.is_active === true;
                    root.querySelector('[data-tracking-status]').textContent = active
                        ? @json(__('Receiving rider location'))
                        : @json(__('Tracking is not active'));
                    root.querySelector('[data-tracking-indicator]').className = `h-3 w-3 rounded-full ${active ? 'animate-pulse bg-emerald-500' : 'bg-gray-300'}`;
                    if (!payload.location) return;
                    const location = root.querySelector('[data-tracking-location]');
                    location.classList.remove('hidden');
                    root.querySelector('[data-tracking-empty]').classList.add('hidden');
                    root.querySelector('[data-tracking-coordinates]').textContent = `${Number(payload.location.latitude).toFixed(6)}, ${Number(payload.location.longitude).toFixed(6)}`;
                    root.querySelector('[data-tracking-time]').textContent = new Date(payload.location.recorded_at).toLocaleString();
                    root.querySelector('[data-tracking-map]').href = `https://www.google.com/maps?q=${payload.location.latitude},${payload.location.longitude}`;
                });
            })();
        </script>
@endif
