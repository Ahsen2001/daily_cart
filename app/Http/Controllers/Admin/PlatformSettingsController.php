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
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value ?? '']
            );
        }

        return redirect()->route('super-admin.settings.index')->with('status', 'Platform configurations updated successfully.');
    }
}
