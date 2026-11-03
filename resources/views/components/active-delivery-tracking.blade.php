@props(['order'])

@php
    $tracking = $order->delivery?->trackingState;
    $trackingIsActive = $tracking?->status === 'active'
        && in_array($order->delivery?->status, \App\Services\DeliveryTrackingService::ACTIVE_STATUSES, true)
        && ! in_array($order->order_status, ['cancelled', 'refunded', 'delivered'], true);
    $currentLocation = $trackingIsActive ? $order->delivery?->currentLocation : null;
    $locationAvailable = $currentLocation?->recorded_at
        && $currentLocation?->latitude !== null
        && $currentLocation?->longitude !== null;
    $freshness = app(\App\Services\DeliveryLocationFreshness::class)->describe(
        $currentLocation?->recorded_at,
        $trackingIsActive,
        in_array($order->order_status, ['delivered', 'cancelled', 'refunded'], true)
            ? $order->order_status
            : $order->delivery?->status,
    );
    $trackingId = 'active-delivery-tracking-'.$order->id;
@endphp

@if ($order->delivery)
    <section id="{{ $trackingId }}" class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">{{ __('Active delivery tracking') }}</p>
                <p data-tracking-status class="mt-1 text-sm font-semibold text-gray-900">
                    {{ __($freshness['message']) }}
                </p>
            </div>
            <span data-tracking-indicator class="h-3 w-3 rounded-full {{ $trackingIsActive ? 'animate-pulse bg-emerald-500' : 'bg-gray-300' }}"></span>
        </div>
        <div data-tracking-location class="mt-3 {{ $locationAvailable ? '' : 'hidden' }} text-sm text-gray-700">
            <p><span class="font-semibold">{{ __('Coordinates') }}:</span> <span data-tracking-coordinates>{{ $locationAvailable ? number_format((float) $currentLocation->latitude, 6).', '.number_format((float) $currentLocation->longitude, 6) : '' }}</span></p>
            <p class="mt-1"><span class="font-semibold">{{ __('Last update') }}:</span> <span data-tracking-time>{{ $currentLocation?->recorded_at?->format('M d, Y h:i:s A') }}</span></p>
            <p data-tracking-stale class="mt-2 rounded-xl px-3 py-2 text-xs font-bold {{ $freshness['is_outdated'] ? 'bg-orange-100 text-orange-800' : 'hidden' }}">{{ __('Location may be outdated') }}</p>
            <a data-tracking-map target="_blank" rel="noopener noreferrer"
               href="{{ $locationAvailable ? 'https://www.google.com/maps?q='.$currentLocation->latitude.','.$currentLocation->longitude : '#' }}"
               class="mt-3 inline-flex rounded-full bg-emerald-700 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-800">
                {{ __('Open rider location') }}
            </a>
        </div>
        <p data-tracking-empty class="mt-3 text-sm text-gray-500 {{ $locationAvailable ? 'hidden' : '' }}">{{ __($freshness['message']) }}</p>
    </section>

        <script>
            (() => {
                const root = document.getElementById(@json($trackingId));
                if (!root) return;
                let lastRealtimeAt = 0;
                let terminal = false;
                const channelName = @json('delivery.'.$order->delivery->id);
                const echoChannel = window.Echo?.private?.(channelName) || null;
                const stream = !echoChannel && window.EventSource
                    ? new EventSource(@json(route('orders.tracking.stream', $order)))
                    : null;
                const applyTracking = payload => {
                    const active = payload.tracking?.is_active === true;
                    terminal = payload.freshness?.status === 'completed';
                    root.querySelector('[data-tracking-status]').textContent = payload.freshness?.message
                        || (active ? @json(__('Live · updated just now')) : @json(__('Waiting for rider location')));
                    root.querySelector('[data-tracking-indicator]').className = `h-3 w-3 rounded-full ${active ? 'animate-pulse bg-emerald-500' : 'bg-gray-300'}`;
                    const stale = root.querySelector('[data-tracking-stale]');
                    stale.classList.toggle('hidden', payload.freshness?.is_outdated !== true);
                    const location = root.querySelector('[data-tracking-location]');
                    if (!payload.location) {
                        location.classList.add('hidden');
                        const empty = root.querySelector('[data-tracking-empty]');
                        empty.textContent = payload.freshness?.message || @json(__('Waiting for rider location'));
                        empty.classList.remove('hidden');
                        return;
                    }
                    location.classList.remove('hidden');
                    root.querySelector('[data-tracking-empty]').classList.add('hidden');
                    root.querySelector('[data-tracking-coordinates]').textContent = `${Number(payload.location.latitude).toFixed(6)}, ${Number(payload.location.longitude).toFixed(6)}`;
                    root.querySelector('[data-tracking-time]').textContent = new Date(payload.location.recorded_at).toLocaleString();
                    root.querySelector('[data-tracking-map]').href = `https://www.google.com/maps?q=${payload.location.latitude},${payload.location.longitude}`;
                };
                stream?.addEventListener('location', event => {
                    lastRealtimeAt = Date.now();
                    applyTracking(JSON.parse(event.data));
                    if (terminal) stream.close();
                });
                echoChannel?.listen('.delivery.location.updated', payload => {
                    lastRealtimeAt = Date.now();
                    applyTracking(payload);
                    if (terminal) window.Echo?.leave(channelName);
                });
                const poll = window.setInterval(async () => {
                    if (terminal) {
                        window.clearInterval(poll);
                        stream?.close();
                        if (echoChannel) window.Echo?.leave(channelName);
                        return;
                    }
                    if (Date.now() - lastRealtimeAt < 25000) return;
                    try {
                        const response = await fetch(@json(route('orders.tracking.show', $order)), {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (response.ok) applyTracking(await response.json());
                    } catch (_) {
                        // Keep the last known status while the connection recovers.
                    }
                }, {{ max(15, min(30, (int) config('delivery_tracking.poll_seconds', 20))) * 1000 }});
            })();
        </script>
@endif
