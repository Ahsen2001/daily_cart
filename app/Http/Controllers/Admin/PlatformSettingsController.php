<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformSettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();

        // Ensure default structure
        $defaults = [
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => '2525',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'firebase_credentials' => '',
            'google_maps_key' => '',
            'sms_gateway_url' => '',
            'sms_gateway_api_key' => '',
            'payhere_merchant_id' => '',
            'payhere_merchant_secret' => '',
            'currency_code' => 'LKR',
            'currency_symbol' => 'Rs.',
            'timezone' => 'Asia/Colombo',
            'maintenance_mode' => '0',
            'delivery_charge_single_item' => '250.00',
            'delivery_charge_bulk_items' => '200.00',
            'service_charge_rate_percent' => '2.00',
        ];

        foreach ($defaults as $key => $value) {
            if (! isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }

        return view('admin.management.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'string', 'max:20'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', 'max:20'],
            'firebase_credentials' => ['nullable', 'string'],
            'google_maps_key' => ['nullable', 'string', 'max:1000'],
            'sms_gateway_url' => ['nullable', 'string', 'max:1000'],
            'sms_gateway_api_key' => ['nullable', 'string', 'max:1000'],
            'payhere_merchant_id' => ['nullable', 'string', 'max:255'],
            'payhere_merchant_secret' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'maintenance_mode' => ['required', 'in:0,1'],
            'delivery_charge_single_item' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'delivery_charge_bulk_items' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'service_charge_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value ?? '']
            );
        }

        return redirect()->route('super-admin.settings.index')->with('status', 'Platform configurations updated successfully.');
    }
}
