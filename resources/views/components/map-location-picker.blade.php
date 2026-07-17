@props([
    'addressInput' => 'address',
    'latitude' => old('latitude'),
    'longitude' => old('longitude'),
    'formattedAddress' => old('formatted_address'),
])

@php($googleMapsBrowserKey = app(\App\Services\GoogleMapsService::class)->browserKey())

<section class="mt-6 rounded-3xl border border-brand-border bg-brand-light/60 p-4 sm:p-5" data-location-picker data-address-input="{{ $addressInput }}">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-primary text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </span>
                <div>
                    <h2 class="font-extrabold text-brand-text">{{ __('Google Maps location') }}</h2>
                    <p class="text-xs text-brand-muted">{{ __('Optional, but recommended for accurate delivery and account review.') }}</p>
                </div>
            </div>
        </div>
        <button type="button" class="dc-button-secondary shrink-0" data-use-current-location>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>
            {{ __('Use my location') }}
        </button>
    </div>

    <input type="hidden" name="latitude" value="{{ $latitude }}" data-location-latitude>
    <input type="hidden" name="longitude" value="{{ $longitude }}" data-location-longitude>
    <input type="hidden" name="formatted_address" value="{{ $formattedAddress }}" data-location-formatted-address>

    @if ($googleMapsBrowserKey)
        <div class="mt-4 h-72 overflow-hidden rounded-2xl border border-brand-border bg-white" data-location-map role="region" aria-label="{{ __('Select registration location on map') }}"></div>
        <p class="mt-2 text-xs text-brand-muted">{{ __('Search using the address field above, click anywhere on the map, or drag the pin.') }}</p>
    @else
        <div class="dc-flash mt-4 border-amber-200 bg-amber-50 text-amber-900">
            {{ __('Interactive map preview is temporarily unavailable. You can still use your browser location or enter the address manually.') }}
        </div>
    @endif

    <div class="mt-4 flex items-start gap-3 rounded-2xl border border-brand-border bg-white p-3.5" data-location-summary>
        <span class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full bg-slate-300" data-location-dot></span>
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-wide text-brand-muted">{{ __('Selected map location') }}</p>
            <p class="mt-1 break-words text-sm font-semibold text-brand-text" data-location-text>
                {{ $latitude && $longitude ? ($formattedAddress ?: $latitude.', '.$longitude) : __('No map pin selected yet.') }}
            </p>
        </div>
    </div>
    <p class="mt-3 hidden text-sm font-semibold" role="status" aria-live="polite" data-location-status></p>

    <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
    <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
</section>

@once
    <script>
        (() => {
            const setupLocationPicker = () => {
                const picker = document.querySelector('[data-location-picker]');
                if (!picker || picker.dataset.ready === 'true') return;

                picker.dataset.ready = 'true';
                const latitudeInput = picker.querySelector('[data-location-latitude]');
                const longitudeInput = picker.querySelector('[data-location-longitude]');
                const formattedAddressInput = picker.querySelector('[data-location-formatted-address]');
                const locationText = picker.querySelector('[data-location-text]');
                const locationDot = picker.querySelector('[data-location-dot]');
                const status = picker.querySelector('[data-location-status]');
                const currentLocationButton = picker.querySelector('[data-use-current-location]');
                const addressInput = document.getElementById(picker.dataset.addressInput);
                const mapElement = picker.querySelector('[data-location-map]');
                let map;
                let marker;
                let geocoder;

                const showStatus = (message, isError = false) => {
                    status.textContent = message;
                    status.classList.remove('hidden', 'text-brand-dark', 'text-red-700');
                    status.classList.add(isError ? 'text-red-700' : 'text-brand-dark');
                };

                const setLocation = (latitude, longitude, formattedAddress = '') => {
                    const lat = Number(latitude);
                    const lng = Number(longitude);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    latitudeInput.value = lat.toFixed(7);
                    longitudeInput.value = lng.toFixed(7);
                    if (formattedAddress) formattedAddressInput.value = formattedAddress;
                    locationText.textContent = formattedAddress || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    locationDot.classList.remove('bg-slate-300');
                    locationDot.classList.add('bg-brand-primary');
                };

                const reverseGeocode = (position) => {
                    if (!geocoder) {
                        setLocation(position.lat(), position.lng());
                        return;
                    }

                    geocoder.geocode({ location: position }, (results, geocoderStatus) => {
                        const address = geocoderStatus === 'OK' ? (results?.[0]?.formatted_address || '') : '';
                        setLocation(position.lat(), position.lng(), address);
                    });
                };

                window.initDailyCartRegistrationMap = () => {
                    if (!mapElement || !window.google?.maps) return;

                    geocoder = new google.maps.Geocoder();
                    const savedLatitude = Number.parseFloat(latitudeInput.value);
                    const savedLongitude = Number.parseFloat(longitudeInput.value);
                    const hasSavedLocation = Number.isFinite(savedLatitude) && Number.isFinite(savedLongitude);
                    const start = hasSavedLocation
                        ? { lat: savedLatitude, lng: savedLongitude }
                        : { lat: 7.8731, lng: 80.7718 };

                    map = new google.maps.Map(mapElement, {
                        center: start,
                        zoom: hasSavedLocation ? 16 : 8,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: true,
                    });
                    marker = new google.maps.Marker({
                        map,
                        position: start,
                        draggable: true,
                        visible: hasSavedLocation,
                        title: '{{ __('Selected registration location') }}',
                    });

                    const choosePosition = (position) => {
                        marker.setPosition(position);
                        marker.setVisible(true);
                        map.panTo(position);
                        map.setZoom(16);
                        reverseGeocode(position);
                        showStatus('{{ __('Location selected successfully.') }}');
                    };

                    map.addListener('click', (event) => choosePosition(event.latLng));
                    marker.addListener('dragend', () => choosePosition(marker.getPosition()));

                    if (addressInput && google.maps.places) {
                        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                            fields: ['formatted_address', 'geometry'],
                            componentRestrictions: { country: 'lk' },
                        });
                        autocomplete.addListener('place_changed', () => {
                            const place = autocomplete.getPlace();
                            if (!place.geometry?.location) {
                                showStatus('{{ __('Choose an address from the Google suggestions.') }}', true);
                                return;
                            }
                            if (place.formatted_address) addressInput.value = place.formatted_address;
                            formattedAddressInput.value = place.formatted_address || '';
                            choosePosition(place.geometry.location);
                        });
                    }
                };

                currentLocationButton?.addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        showStatus('{{ __('Location services are not supported by this browser.') }}', true);
                        return;
                    }

                    currentLocationButton.disabled = true;
                    showStatus('{{ __('Finding your current location...') }}');
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const coordinates = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };
                            if (map && window.google?.maps) {
                                const googlePosition = new google.maps.LatLng(coordinates.lat, coordinates.lng);
                                marker.setPosition(googlePosition);
                                marker.setVisible(true);
                                map.panTo(googlePosition);
                                map.setZoom(16);
                                reverseGeocode(googlePosition);
                            } else {
                                setLocation(coordinates.lat, coordinates.lng, '{{ __('Current browser location') }}');
                            }
                            showStatus('{{ __('Current location added.') }}');
                            currentLocationButton.disabled = false;
                        },
                        () => {
                            showStatus('{{ __('Location permission was not granted. Select a point on the map instead.') }}', true);
                            currentLocationButton.disabled = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 },
                    );
                });

                if (latitudeInput.value && longitudeInput.value) {
                    locationDot.classList.remove('bg-slate-300');
                    locationDot.classList.add('bg-brand-primary');
                }
            };

            setupLocationPicker();
        })();
    </script>

    @if ($googleMapsBrowserKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsBrowserKey) }}&libraries=places&loading=async&callback=initDailyCartRegistrationMap"></script>
    @endif
@endonce
