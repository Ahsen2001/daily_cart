<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Checkout') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="p-4 mb-6 text-sm font-medium text-green-700 bg-white shadow-sm sm:rounded-lg">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-6 text-sm font-medium text-red-700 bg-white shadow-sm sm:rounded-lg">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="p-4 bg-white shadow-sm sm:p-6 xl:col-span-2 sm:rounded-lg">
                    <form method="POST" action="{{ route('customer.checkout.store') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="coupon_code" value="{{ $couponCode }}">
                        <input type="hidden" name="loyalty_points" value="{{ $loyaltyPoints }}">

                        <div>
                            <x-input-label for="delivery_address" :value="__('Delivery Address')" />
                            <textarea id="delivery_address" name="delivery_address" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('delivery_address', Auth::user()->customer?->address_line_1.', '.Auth::user()->customer?->city.', '.Auth::user()->customer?->district) }}</textarea>
                            <input type="hidden" id="delivery_latitude" name="delivery_latitude" value="{{ old('delivery_latitude', Auth::user()->customer?->latitude) }}">
                            <input type="hidden" id="delivery_longitude" name="delivery_longitude" value="{{ old('delivery_longitude', Auth::user()->customer?->longitude) }}">
                            <input type="hidden" id="delivery_distance_meters" name="delivery_distance_meters" value="{{ old('delivery_distance_meters') }}">
                            <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
                            @if ($googleMapsBrowserKey)
                                <div class="mt-3 rounded-2xl border border-green-100 bg-green-50 p-3">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-xs text-gray-600">{{ __('Google Maps is optional. Use it only when you want to pin the delivery location.') }}</p>
                                        <button id="load-delivery-map" type="button" class="rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-700">{{ __('Use Map') }}</button>
                                    </div>
                                    <div id="delivery-map-error" class="mt-3 hidden rounded-xl bg-orange-50 p-3 text-xs text-orange-700">{{ __('Google Maps could not load. Please check the API key, billing, enabled APIs, and HTTP referrer restrictions.') }}</div>
                                    <div id="delivery-map" class="mt-3 hidden h-64 rounded-2xl border border-green-100 bg-white"></div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <x-input-label for="scheduled_delivery_at" :value="__('Scheduled Delivery Time')" />
                            <div class="mt-1 rounded-2xl bg-green-50 p-3 text-sm text-gray-700">
                                <div>{{ __('Current Date and Time') }}: <span class="font-semibold">{{ $currentDateTime->format('M d, Y h:i A') }}</span></div>
                                <div>{{ __('Earliest Delivery Time') }}: <span class="font-semibold">{{ $minimumDeliveryTime->format('M d, Y h:i A') }}</span></div>
                            </div>
                            <x-text-input id="scheduled_delivery_at" name="scheduled_delivery_at" type="datetime-local" class="block w-full mt-1" min="{{ $minimumDeliveryTime->format('Y-m-d\TH:i') }}" :value="old('scheduled_delivery_at', $minimumDeliveryTime->format('Y-m-d\TH:i'))" required />
                            <p class="mt-2 text-xs text-gray-500">{{ __('Delivery time must be at least 30 minutes after placing the order.') }}</p>
                            <x-input-error :messages="$errors->get('scheduled_delivery_at')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_method" :value="__('Payment Method')" />
                            <select id="payment_method" name="payment_method" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="cash_on_delivery" @selected(old('payment_method') === 'cash_on_delivery')>{{ __('Cash on Delivery') }}</option>
                                <option value="card" @selected(old('payment_method') === 'card')>{{ __('Card Payment') }}</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>{{ __('Bank Transfer') }}</option>
                                <option value="wallet" @selected(old('payment_method') === 'wallet')>{{ __('Wallet') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </div>

                        <x-primary-button>{{ __('Place Order') }}</x-primary-button>
                    </form>
                </div>

                <div class="p-4 bg-white shadow-sm sm:p-6 sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">{{ __('Order Summary') }}</h3>

                    <form method="POST" action="{{ route('customer.checkout.coupon') }}" class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                        @csrf
                        <x-text-input class="w-full" name="coupon_code" placeholder="Coupon code" :value="$couponCode" />
                        <x-secondary-button type="submit" class="w-full justify-center whitespace-nowrap sm:w-auto">{{ __('Apply') }}</x-secondary-button>
                    </form>

                    <form method="POST" action="{{ route('customer.checkout.loyalty') }}" class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                        @csrf
                        <x-text-input class="w-full" name="loyalty_points" type="number" min="0" placeholder="Loyalty points" :value="$loyaltyPoints" />
                        <x-secondary-button type="submit" class="w-full justify-center whitespace-nowrap sm:w-auto">{{ __('Redeem') }}</x-secondary-button>
                    </form>

                    <dl class="mt-6 space-y-3 text-sm">
                        <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['subtotal']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Discount') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['discount']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Loyalty Discount') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['loyalty_discount']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['delivery_fee']) }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['service_charge']) }}</dd></div>
                        <div class="flex justify-between border-t border-gray-100 pt-3 text-base font-semibold"><dt>{{ __('Grand Total') }}</dt><dd>{{ \App\Services\CurrencyService::formatLkr($quote['grand_total']) }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if ($googleMapsBrowserKey)
        <script>
            let dailyCartMapLoaded = false;

            window.gm_authFailure = function () {
                document.getElementById('delivery-map')?.classList.add('hidden');
                document.getElementById('delivery-map-error')?.classList.remove('hidden');
            };

            window.initDailyCartMap = function () {
                const addressInput = document.getElementById('delivery_address');
                const latInput = document.getElementById('delivery_latitude');
                const lngInput = document.getElementById('delivery_longitude');
                const mapElement = document.getElementById('delivery-map');
                mapElement.classList.remove('hidden');
                const start = {
                    lat: parseFloat(latInput.value) || 7.8731,
                    lng: parseFloat(lngInput.value) || 80.7718,
                };
                const map = new google.maps.Map(mapElement, {
                    center: start,
                    zoom: latInput.value && lngInput.value ? 15 : 8,
                    mapTypeControl: false,
                    streetViewControl: false,
                });
                const marker = new google.maps.Marker({
                    position: start,
                    map,
                    draggable: true,
                });
                const geocoder = new google.maps.Geocoder();
                const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                    componentRestrictions: { country: 'lk' },
                    fields: ['formatted_address', 'geometry'],
                });

                const setPosition = (location, formattedAddress = null) => {
                    latInput.value = location.lat();
                    lngInput.value = location.lng();
                    marker.setPosition(location);
                    map.setCenter(location);
                    map.setZoom(16);
                    if (formattedAddress) {
                        addressInput.value = formattedAddress;
                    }
                };

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (place.geometry?.location) {
                        setPosition(place.geometry.location, place.formatted_address);
                    }
                });

                marker.addListener('dragend', () => {
                    const position = marker.getPosition();
                    setPosition(position);
                    geocoder.geocode({ location: position }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            addressInput.value = results[0].formatted_address;
                        }
                    });
                });
            };

            document.getElementById('load-delivery-map')?.addEventListener('click', () => {
                if (dailyCartMapLoaded) {
                    document.getElementById('delivery-map')?.classList.remove('hidden');
                    return;
                }

                dailyCartMapLoaded = true;
                const script = document.createElement('script');
                script.async = true;
                script.defer = true;
                script.onerror = () => {
                    document.getElementById('delivery-map-error')?.classList.remove('hidden');
                };
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googleMapsBrowserKey }}&libraries=places&callback=initDailyCartMap';
                document.head.appendChild(script);
            });
        </script>
    @endif
</x-app-layout>
