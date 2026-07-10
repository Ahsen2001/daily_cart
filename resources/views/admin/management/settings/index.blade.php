<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Platform Settings & Configurations') }}</h2>
            <a href="{{ route('super-admin.dashboard') }}" class="text-sm font-medium text-indigo-700 underline">{{ __('Back to Dashboard') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 shadow-sm">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- SMTP Settings Card -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">{{ __('SMTP Configuration') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="smtp_host" :value="__('SMTP Host')" />
                            <x-text-input id="smtp_host" name="smtp_host" type="text" class="mt-1 block w-full" :value="$settings['smtp_host']" />
                        </div>
                        <div>
                            <x-input-label for="smtp_port" :value="__('SMTP Port')" />
                            <x-text-input id="smtp_port" name="smtp_port" type="text" class="mt-1 block w-full" :value="$settings['smtp_port']" />
                        </div>
                        <div>
                            <x-input-label for="smtp_username" :value="__('SMTP Username')" />
                            <x-text-input id="smtp_username" name="smtp_username" type="text" class="mt-1 block w-full" :value="$settings['smtp_username']" />
                        </div>
                        <div>
                            <x-input-label for="smtp_password" :value="__('SMTP Password')" />
                            <x-text-input id="smtp_password" name="smtp_password" type="password" class="mt-1 block w-full" :value="$settings['smtp_password']" />
                        </div>
                        <div>
                            <x-input-label for="smtp_encryption" :value="__('SMTP Encryption')" />
                            <x-text-input id="smtp_encryption" name="smtp_encryption" type="text" class="mt-1 block w-full" :value="$settings['smtp_encryption']" />
                        </div>
                    </div>
                </div>

                <!-- Firebase & Google Maps Settings -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">{{ __('Third-Party Integration Keys') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="google_maps_key" :value="__('Google Maps API Browser Key')" />
                            <x-text-input id="google_maps_key" name="google_maps_key" type="text" class="mt-1 block w-full" :value="$settings['google_maps_key']" />
                        </div>
                        <div>
                            <x-input-label for="firebase_credentials" :value="__('Firebase Private Credentials (JSON)')" />
                            <textarea id="firebase_credentials" name="firebase_credentials" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">{{ $settings['firebase_credentials'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway & SMS Settings -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">{{ __('SMS Gateway & Payment Gateway (PayHere)') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="sms_gateway_url" :value="__('SMS API URL')" />
                            <x-text-input id="sms_gateway_url" name="sms_gateway_url" type="text" class="mt-1 block w-full" :value="$settings['sms_gateway_url']" />
                        </div>
                        <div>
                            <x-input-label for="sms_gateway_api_key" :value="__('SMS API Key')" />
                            <x-text-input id="sms_gateway_api_key" name="sms_gateway_api_key" type="text" class="mt-1 block w-full" :value="$settings['sms_gateway_api_key']" />
                        </div>
                        <div>
                            <x-input-label for="payhere_merchant_id" :value="__('PayHere Merchant ID')" />
                            <x-text-input id="payhere_merchant_id" name="payhere_merchant_id" type="text" class="mt-1 block w-full" :value="$settings['payhere_merchant_id']" />
                        </div>
                        <div>
                            <x-input-label for="payhere_merchant_secret" :value="__('PayHere Merchant Secret')" />
                            <x-text-input id="payhere_merchant_secret" name="payhere_merchant_secret" type="password" class="mt-1 block w-full" :value="$settings['payhere_merchant_secret']" />
                        </div>
                    </div>
                </div>

                <!-- Currency, Timezone & Maintenance Settings -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">{{ __('System Parameter Configurations') }}</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="currency_code" :value="__('Currency Code')" />
                            <x-text-input id="currency_code" name="currency_code" type="text" class="mt-1 block w-full" :value="$settings['currency_code']" />
                        </div>
                        <div>
                            <x-input-label for="currency_symbol" :value="__('Currency Symbol')" />
                            <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1 block w-full" :value="$settings['currency_symbol']" />
                        </div>
                        <div>
                            <x-input-label for="timezone" :value="__('Timezone')" />
                            <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="$settings['timezone']" />
                        </div>
                    </div>
                    
                    <div class="mt-4 border-t pt-4">
                        <x-input-label for="maintenance_mode" :value="__('Maintenance Mode')" />
                        <select id="maintenance_mode" name="maintenance_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0" @selected($settings['maintenance_mode'] === '0')>{{ __('Disabled (Site Live)') }}</option>
                            <option value="1" @selected($settings['maintenance_mode'] === '1')>{{ __('Enabled (Maintenance Message Screen)') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="submit" class="rounded bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-700">
                        {{ __('Save Configurations') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
