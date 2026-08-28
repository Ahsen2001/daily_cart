<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Delivery Simulator') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Test active delivery pricing by zone, district, province, address, and distance.') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('super-admin.delivery.simulator') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex flex-col gap-2 border-b border-gray-100 pb-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Test a delivery fee') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Type a full address to detect an active zone first, then a configured district, then a configured province. You can also select a zone manually.') }}</p>
                    </div>
                    <span class="w-fit rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">{{ __('Read-only simulation') }}</span>
                </div>

                @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">{{ __('Please correct the simulator inputs.') }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="simulator-subtotal" :value="__('Cart subtotal (Rs.)')" />
                        <x-text-input
                            id="simulator-subtotal"
                            name="subtotal"
                            type="number"
                            min="0"
                            step="0.01"
                            :value="request('subtotal')"
                            class="mt-2 block w-full"
                            placeholder="2500.00"
                            required />
                    </div>

                    <div>
                        <x-input-label for="simulator-zone" :value="__('Delivery zone')" />
                        <select
                            id="simulator-zone"
                            name="zone_id"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Auto-detect from full address') }}</option>
                            @foreach ($zones as $zone)
                            <option
                                value="{{ $zone->id }}"
                                data-district="{{ $zone->district }}"
                                data-province="{{ $zone->province }}"
                                @selected((string) request('zone_id') === (string) $zone->id)>
                                {{ $zone->name }} — {{ $zone->district }}{{ $zone->province ? ', '.$zone->province : '' }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">{{ __('A manually selected active zone overrides automatic address detection.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="simulator-district" :value="__('District')" />
                        <x-text-input id="simulator-district" name="district" :value="$selectedZone?->district ?? request('district')" class="mt-2 block w-full" placeholder="Batticaloa" />
                    </div>

                    <div>
                        <x-input-label for="simulator-province" :value="__('Province')" />
                        <x-text-input id="simulator-province" name="province" :value="$selectedZone?->province ?? request('province')" class="mt-2 block w-full" placeholder="Eastern Province" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="simulator-full-address" :value="__('Full delivery address')" />
                        <textarea
                            id="simulator-full-address"
                            name="full_address"
                            rows="3"
                            maxlength="1000"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('House or building number, street, town, district, and postal code') }}">{{ request('full_address') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">{{ __('The simulator matches this text against active zone names first, districts second, and provinces third. The address is not saved.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="simulator-distance" :value="__('Distance from store (meters)')" />
                        <x-text-input
                            id="simulator-distance"
                            name="distance_meters"
                            type="number"
                            min="0"
                            step="1"
                            :value="request('distance_meters')"
                            class="mt-2 block w-full"
                            placeholder="5000" />
                        <p class="mt-2 text-xs text-gray-500">{{ __('Per-kilometre charges and maximum-distance rules use this value.') }}</p>
                    </div>

                    <div class="flex items-end">
                        <x-primary-button class="w-full justify-center py-3">{{ __('Test delivery fee') }}</x-primary-button>
                    </div>
                </div>
            </form>

            @if ($estimate)
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5" aria-labelledby="quote-result-title">
                <div class="border-b border-gray-100 bg-gradient-to-r from-green-50 to-white px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-green-700">{{ __('Simulation result') }}</p>
                    <h3 id="quote-result-title" class="mt-1 text-xl font-bold text-gray-900">{{ __('Delivery quote') }}</h3>
                </div>

                <dl class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        [__('Delivery fee'), 'Rs. '.number_format($estimate['delivery_fee'], 2)],
                        [__('Service charge'), 'Rs. '.number_format($simulation['service_charge'], 2)],
                        [__('Customer total'), 'Rs. '.number_format($simulation['customer_total'], 2)],
                        [__('Estimated time'), $estimate['estimated_delivery_minutes'] ? $estimate['estimated_delivery_minutes'].' min' : __('Not configured')],
                        [__('Rider earnings'), 'Rs. '.number_format($simulation['rider_earnings'], 2)],
                        [__('Platform delivery margin'), 'Rs. '.number_format($simulation['platform_delivery_margin'], 2)],
                    ] as [$label, $value])
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <dt class="text-sm text-gray-500">{{ $label }}</dt>
                        <dd class="mt-1 text-xl font-bold text-gray-900">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>

                <dl class="grid gap-5 border-t border-gray-100 px-6 py-5 text-sm sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Selected zone') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $simulation['zone_name'] ?: __('Not selected') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('District / Province') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $simulation['district'] ?: '—' }}{{ $simulation['province'] ? ' / '.$simulation['province'] : '' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Location identified by') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900">
                            {{ $simulation['location_source'] ? ucfirst($simulation['location_source']) : __('Default pricing') }}
                            @if ($simulation['matched_location'])
                            <span class="block text-xs font-normal text-gray-500">{{ $simulation['matched_location'] }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Matched rule') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $estimate['rule_scope'])) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">{{ __('Free delivery') }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $estimate['free_delivery_eligible'] ? __('Eligible') : __('Not eligible') }}</dd>
                    </div>
                </dl>

                @if ($simulation['full_address'] !== '')
                <dl class="border-t border-gray-100 px-6 py-5">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Test delivery address') }}</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm font-semibold leading-6 text-gray-900">{{ $simulation['full_address'] }}</dd>
                    <dd class="mt-2 text-xs text-gray-500">{{ __('Distance tested:') }} {{ number_format($simulation['distance_meters'] / 1000, 2) }} km</dd>
                </dl>
                @endif
            </section>
            @endif
        </div>
    </div>

    <script>
        (() => {
            const zoneSelect = document.getElementById('simulator-zone');
            const districtInput = document.getElementById('simulator-district');
            const provinceInput = document.getElementById('simulator-province');

            if (!zoneSelect || !districtInput || !provinceInput) {
                return;
            }

            zoneSelect.addEventListener('change', () => {
                const option = zoneSelect.options[zoneSelect.selectedIndex];

                if (!option?.value) {
                    districtInput.value = '';
                    provinceInput.value = '';
                    return;
                }

                districtInput.value = option.dataset.district || '';
                provinceInput.value = option.dataset.province || '';
            });
        })();
    </script>
</x-app-layout>
