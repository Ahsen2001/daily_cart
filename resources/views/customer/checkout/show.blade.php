@php
    use App\Services\CurrencyService;

    $customer = Auth::user()->customer;
    $addressModel = $customer?->addresses()->where('is_default', true)->first() ?? $customer?->addresses()->first();
    $defaultAddress = $addressModel ? collect([
        $addressModel->address_line_1,
        $addressModel->city,
        $addressModel->district,
    ])->filter()->implode(', ') : '';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="dc-page-eyebrow">{{ __('Final step') }}</p><h2 class="dc-page-title">{{ __('Secure Checkout') }}</h2></div>
            <a href="{{ route('customer.cart.index') }}" class="dc-button-secondary">{{ __('Back to cart') }}</a>
        </div>
    </x-slot>

    <div class="dc-page-section">
        <div class="dc-container">
            @if (session('status'))
                <div class="dc-flash dc-flash-success mb-6" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="dc-flash dc-flash-error mb-6" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="mb-6 rounded-3xl border border-green-100 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-green-700">{{ __('DailyCart checkout') }}</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ __('Review delivery and payment') }}</h1>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                        <div class="rounded-full bg-green-600 px-3 py-2 text-white">{{ __('Cart') }}</div>
                        <div class="rounded-full bg-green-600 px-3 py-2 text-white">{{ __('Checkout') }}</div>
                        <div class="rounded-full bg-gray-100 px-3 py-2 text-gray-500">{{ __('Confirm') }}</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                <div class="space-y-5">
                    <form id="checkout-form" method="POST" action="{{ route('customer.checkout.store') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="coupon_code" value="{{ $couponCode }}">
                        <input type="hidden" name="loyalty_points" value="{{ $loyaltyPoints }}">
                        <input type="hidden" id="client_current_at" name="client_current_at" value="{{ old('client_current_at') }}">

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-5 flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">{{ __('Contact Information') }}</h2>
                                    <p class="text-sm text-gray-500">{{ __('We use this account information for secure order updates.') }}</p>
                                </div>
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">{{ __('Verified customer') }}</span>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label :value="__('Name')" />
                                    <x-text-input class="mt-1 block w-full bg-gray-50" type="text" :value="Auth::user()->name" disabled />
                                </div>
                                <div>
                                    <x-input-label :value="__('Email')" />
                                    <x-text-input class="mt-1 block w-full bg-gray-50" type="email" :value="Auth::user()->email" disabled />
                                </div>
                                <div>
                                    <x-input-label :value="__('Phone')" />
                                    <x-text-input class="mt-1 block w-full bg-gray-50" type="text" :value="Auth::user()->customer?->phone ?? Auth::user()->phone" disabled />
                                </div>
                                <div>
                                    <x-input-label :value="__('Currency')" />
                                    <x-text-input class="mt-1 block w-full bg-gray-50" type="text" value="LKR only" disabled />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-5">
                                <h2 class="text-lg font-bold text-gray-900">{{ __('Delivery Address') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('Enter a complete Sri Lankan delivery address. Google Maps is optional.') }}</p>
                            </div>

                            <textarea id="delivery_address" name="delivery_address" rows="4" class="block w-full rounded-2xl border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500" required placeholder="{{ __('House number, street, city, district') }}">{{ old('delivery_address', $defaultAddress) }}</textarea>
                            @if ($deliveryDistricts->isNotEmpty())
                                <div class="mt-4">
                                    <x-input-label for="delivery_district" :value="__('Delivery District')" />
                                    <select id="delivery_district" name="delivery_district" class="mt-1 block w-full rounded-2xl border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                                        <option value="">{{ __('Select the district used for delivery pricing') }}</option>
                                        @if ($selectedDeliveryDistrict && ! $deliveryDistricts->contains(fn ($district) => strcasecmp($district, $selectedDeliveryDistrict) === 0))
                                            <option value="{{ $selectedDeliveryDistrict }}" selected>{{ $selectedDeliveryDistrict }}</option>
                                        @endif
                                        @foreach ($deliveryDistricts as $district)
                                            <option value="{{ $district }}" @selected(old('delivery_district', $selectedDeliveryDistrict) === $district)>{{ $district }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-gray-500">{{ __('The active Delivery Management rule for this district determines the base fee, per-kilometre charge, minimum order, and free-delivery eligibility.') }}</p>
                                    <x-input-error :messages="$errors->get('delivery_district')" class="mt-2" />
                                </div>
                            @else
                                <input type="hidden" name="delivery_district" value="{{ old('delivery_district', $selectedDeliveryDistrict) }}">
                            @endif
                            <input type="hidden" id="delivery_latitude" name="delivery_latitude" value="{{ old('delivery_latitude', $addressModel?->latitude) }}">
                            <input type="hidden" id="delivery_longitude" name="delivery_longitude" value="{{ old('delivery_longitude', $addressModel?->longitude) }}">
                            <input type="hidden" id="delivery_distance_meters" name="delivery_distance_meters" value="{{ old('delivery_distance_meters') }}">
                            <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />

                            @if ($googleMapsBrowserKey)
                                <div class="mt-4 rounded-3xl border border-green-100 bg-green-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ __('Pin delivery location') }}</p>
                                            <p class="text-xs text-gray-600">{{ __('Use this only when you want more accurate rider tracking.') }}</p>
                                        </div>
                                        <button id="load-delivery-map" type="button" class="rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-700">{{ __('Use Map') }}</button>
                                    </div>
                                    <div id="delivery-map-error" class="mt-3 hidden rounded-2xl bg-orange-50 p-3 text-xs text-orange-700">{{ __('Google Maps could not load. Check API key, billing, APIs, and HTTP referrer restrictions.') }}</div>
                                    <div id="delivery-map" class="mt-3 hidden h-72 rounded-2xl border border-green-100 bg-white"></div>
                                </div>
                            @endif
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-5">
                                <h2 class="text-lg font-bold text-gray-900">{{ __('Delivery Schedule') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('The earliest delivery time is automatically calculated from the current order time.') }}</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-green-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-green-700">{{ __('Current date and time') }}</p>
                                    <p id="client-current-time-display" class="mt-1 font-semibold text-gray-900">{{ $currentDateTime->format('M d, Y h:i A') }}</p>
                                </div>
                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-orange-600">{{ __('Earliest delivery time') }}</p>
                                    <p id="minimum-delivery-time-display" class="mt-1 font-semibold text-gray-900">{{ $minimumDeliveryTime->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="scheduled_delivery_at" :value="__('Select Delivery Time')" />
                                <x-text-input id="scheduled_delivery_at" name="scheduled_delivery_at" type="datetime-local" class="mt-1 block w-full" min="{{ $minimumDeliveryTime->format('Y-m-d\TH:i') }}" :value="old('scheduled_delivery_at', $minimumDeliveryTime->format('Y-m-d\TH:i'))" required />
                                <p class="mt-2 text-xs text-gray-500">{{ __('Delivery time must be at least 30 minutes after placing the order.') }}</p>
                                <x-input-error :messages="$errors->get('scheduled_delivery_at')" class="mt-2" />
                            </div>
                        </section>

                        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-5">
                                <h2 class="text-lg font-bold text-gray-900">{{ __('Payment Method') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('PayHere is used for card payments. Cash on Delivery remains pending until delivery is completed.') }}</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ([
                                    'cash_on_delivery' => __('Cash on Delivery'),
                                    'card' => __('Card Payment'),
                                    'bank_transfer' => __('Bank Transfer'),
                                    'wallet' => __('Wallet'),
                                ] as $value => $label)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-brand-border bg-brand-light p-4 transition has-[:checked]:border-brand-primary has-[:checked]:bg-green-50 hover:border-brand-primary/40">
                                        <input type="radio" name="payment_method" value="{{ $value }}" class="text-green-600 focus:ring-green-500" @checked(old('payment_method', 'cash_on_delivery') === $value)>
                                        <span class="text-sm font-semibold text-gray-800">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                        </section>
                    </form>
                </div>

                <aside class="space-y-5">
                    <section class="sticky top-24 rounded-3xl border border-brand-border bg-white p-5 shadow-soft sm:p-6">
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Your Order') }}</h2>

                        <div class="mt-5 max-h-[360px] space-y-4 overflow-y-auto pr-1">
                            @forelse ($cart->items as $item)
                                <div class="flex gap-3 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                    <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-sm font-bold text-green-700">
                                        {{ strtoupper(substr($item->product->name, 0, 2)) }}
                                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-900 px-1 text-[10px] font-bold text-white">{{ $item->quantity }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->variant?->name ?? __('Default') }}</p>
                                        <p class="mt-1 text-xs text-green-700">{{ CurrencyService::formatLkr($item->unit_price) }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">{{ CurrencyService::formatLkr((float) $item->unit_price * $item->quantity) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('Your cart is empty.') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-5 border-t border-gray-100 pt-5">
                            <form method="POST" action="{{ route('customer.checkout.coupon') }}" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] xl:grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_auto]">
                                @csrf
                                <x-text-input class="w-full" name="coupon_code" placeholder="Coupon code" :value="$couponCode" />
                                <x-secondary-button type="submit" class="w-full justify-center whitespace-nowrap">{{ __('Apply') }}</x-secondary-button>
                            </form>

                            <form method="POST" action="{{ route('customer.checkout.loyalty') }}" class="mt-3 grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] xl:grid-cols-1 2xl:grid-cols-[minmax(0,1fr)_auto]">
                                @csrf
                                <x-text-input class="w-full" name="loyalty_points" type="number" min="0" placeholder="Loyalty points" :value="$loyaltyPoints" />
                                <x-secondary-button type="submit" class="w-full justify-center whitespace-nowrap">{{ __('Redeem') }}</x-secondary-button>
                            </form>
                        </div>

                        <dl class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between"><dt>{{ __('Subtotal') }}</dt><dd>{{ CurrencyService::formatLkr($quote['subtotal']) }}</dd></div>
                            <div class="flex justify-between text-green-700"><dt>{{ __('Discount') }}</dt><dd>{{ CurrencyService::formatLkr($quote['discount']) }}</dd></div>
                            <div class="flex justify-between text-green-700"><dt>{{ __('Loyalty Discount') }}</dt><dd>{{ CurrencyService::formatLkr($quote['loyalty_discount']) }}</dd></div>
                            <div class="flex justify-between"><dt>{{ __('Delivery Charge') }}</dt><dd>{{ CurrencyService::formatLkr($quote['delivery_fee']) }}</dd></div>
                            @if ($quote['estimated_delivery_minutes'])
                                <div class="flex justify-between text-brand-muted"><dt>{{ __('Estimated delivery') }}</dt><dd>{{ $quote['estimated_delivery_minutes'] }} {{ __('minutes') }}</dd></div>
                            @endif
                            @if ($quote['free_delivery_eligible'])
                                <div class="rounded-lg bg-green-50 px-3 py-2 text-sm font-semibold text-green-700">{{ __('You qualify for free delivery.') }}</div>
                            @endif
                            <div class="flex justify-between"><dt>{{ __('Service Charge') }}</dt><dd>{{ CurrencyService::formatLkr($quote['service_charge']) }}</dd></div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 text-lg font-bold text-gray-900"><dt>{{ __('Grand Total') }}</dt><dd>{{ CurrencyService::formatLkr($quote['grand_total']) }}</dd></div>
                        </dl>

                        <button form="checkout-form" type="submit" class="dc-button mt-6 w-full">
                            {{ __('Place Secure Order') }}
                        </button>
                        <p class="mt-3 text-center text-xs text-gray-500">{{ __('Secure checkout. LKR payments only. Delivery scheduling is validated on the server.') }}</p>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    <div id="bank-transfer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-950/60 px-4 py-6">
        <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-orange-600">{{ __('Bank Transfer') }}</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">{{ __('DailyCart bank account details') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Use one of these accounts, then keep your transfer slip for confirmation.') }}</p>
                </div>
                <button type="button" data-close-bank-modal class="rounded-full bg-gray-100 px-3 py-1 text-sm font-bold text-gray-600 transition hover:bg-gray-200">&times;</button>
            </div>

            <div class="mt-5 grid gap-3">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 text-sm text-gray-800">
                    <p>UMER AHSEN</p>
                    <p class="font-bold text-gray-900">{{ __('Peoples Bank') }}</p>
                    <p>{{ __('Account') }}: 167200230025623</p>
                    
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 text-sm text-gray-800">
                    <p>UMER AHSEN</p>
                    <p class="font-bold text-gray-900">{{ __('Commercial Bank') }}</p>
                    <p>{{ __('Account') }}: 8018339778</p>
                    <p>{{ __('Branch') }}: 159 - Valaichchenai</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 text-sm text-gray-800">
                    <p>UMER AHSEN</p>
                    <p class="font-bold text-gray-900">{{ __('Amana Bank') }}</p>
                    <p>{{ __('Account') }}: 0110118699003</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="button" data-close-bank-modal class="inline-flex flex-1 justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-700">{{ __('I Understand') }}</button>
                <button type="submit" form="checkout-form" class="inline-flex flex-1 justify-center rounded-full bg-gray-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">{{ __('Place Order') }}</button>
            </div>
        </div>
    </div>

    @if ($googleMapsBrowserKey)
        <script>
            window.initDailyCartMap = function () {
                const addressInput = document.getElementById('delivery_address');
                const latInput = document.getElementById('delivery_latitude');
                const lngInput = document.getElementById('delivery_longitude');
                const mapElement = document.getElementById('delivery-map');
                const mapError = document.getElementById('delivery-map-error');

                if (! addressInput || ! latInput || ! lngInput || ! mapElement || ! window.google?.maps) {
                    mapError?.classList.remove('hidden');
                    return;
                }

                mapElement.classList.remove('hidden');
                mapError?.classList.add('hidden');

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
                const mapElement = document.getElementById('delivery-map');
                const mapError = document.getElementById('delivery-map-error');

                if (window.google?.maps) {
                    window.initDailyCartMap();
                    return;
                }

                mapElement?.classList.remove('hidden');
                mapError?.classList.add('hidden');

                if (document.getElementById('dailycart-google-maps-script')) {
                    return;
                }

                const script = document.createElement('script');
                script.id = 'dailycart-google-maps-script';
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googleMapsBrowserKey }}&libraries=places&callback=initDailyCartMap';
                script.async = true;
                script.defer = true;
                script.onerror = () => {
                    mapElement?.classList.add('hidden');
                    mapError?.classList.remove('hidden');
                };
                document.head.appendChild(script);
            });
        </script>
    @endif

    <script>
        const scheduleInput = document.getElementById('scheduled_delivery_at');
        const clientCurrentInput = document.getElementById('client_current_at');
        const currentTimeDisplay = document.getElementById('client-current-time-display');
        const minimumTimeDisplay = document.getElementById('minimum-delivery-time-display');
        const checkoutForm = document.getElementById('checkout-form');
        const deliveryDistrict = document.getElementById('delivery_district');

        deliveryDistrict?.addEventListener('change', () => {
            const url = new URL(window.location.href);

            if (deliveryDistrict.value) {
                url.searchParams.set('delivery_district', deliveryDistrict.value);
            } else {
                url.searchParams.delete('delivery_district');
            }

            window.location.assign(url.toString());
        });

        const padDatePart = (value) => String(value).padStart(2, '0');
        const toLocalInputValue = (date) => {
            return [
                date.getFullYear(),
                padDatePart(date.getMonth() + 1),
                padDatePart(date.getDate()),
            ].join('-') + 'T' + [
                padDatePart(date.getHours()),
                padDatePart(date.getMinutes()),
            ].join(':');
        };
        const formatDisplayTime = (date) => new Intl.DateTimeFormat(undefined, {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
        const deviceNowAtMinute = () => {
            const date = new Date();
            date.setSeconds(0, 0);
            return date;
        };
        const refreshDeliverySchedule = () => {
            if (!scheduleInput || !clientCurrentInput) return;

            const now = deviceNowAtMinute();
            const minimum = new Date(now.getTime() + 30 * 60 * 1000);
            const currentValue = scheduleInput.value;
            const minimumValue = toLocalInputValue(minimum);

            clientCurrentInput.value = toLocalInputValue(now);
            scheduleInput.min = minimumValue;

            if (!currentValue || currentValue < minimumValue) {
                scheduleInput.value = minimumValue;
            }

            if (currentTimeDisplay) {
                currentTimeDisplay.textContent = formatDisplayTime(now);
            }

            if (minimumTimeDisplay) {
                minimumTimeDisplay.textContent = formatDisplayTime(minimum);
            }
        };

        refreshDeliverySchedule();
        window.setInterval(refreshDeliverySchedule, 60 * 1000);
        checkoutForm?.addEventListener('submit', refreshDeliverySchedule);

        const bankModal = document.getElementById('bank-transfer-modal');
        const openBankModal = () => {
            bankModal?.classList.remove('hidden');
            bankModal?.classList.add('flex');
        };
        const closeBankModal = () => {
            bankModal?.classList.add('hidden');
            bankModal?.classList.remove('flex');
        };

        document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
            input.addEventListener('change', () => {
                if (input.value === 'bank_transfer' && input.checked) {
                    openBankModal();
                }
            });
        });

        document.querySelectorAll('[data-close-bank-modal]').forEach((button) => {
            button.addEventListener('click', closeBankModal);
        });

        bankModal?.addEventListener('click', (event) => {
            if (event.target === bankModal) {
                closeBankModal();
            }
        });
    </script>
</x-app-layout>
