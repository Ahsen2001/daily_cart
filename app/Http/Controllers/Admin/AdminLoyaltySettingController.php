<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLoyaltySettingRequest;
use App\Models\Customer;
use App\Services\LoyaltyPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminLoyaltySettingController extends Controller
{
    public function edit(LoyaltyPointService $loyalty): View
    {
        return view('admin.loyalty.edit', [
            'setting' => $loyalty->setting(),
            'customers' => Customer::with('user')->paginate(20),
        ]);
    }

    public function update(UpdateLoyaltySettingRequest $request, LoyaltyPointService $loyalty): RedirectResponse
    {
        $setting = $loyalty->setting();
        $setting->update($request->validated() + ['updated_by' => $request->user()->id]);

        return back()->with('status', 'Loyalty settings updated.');
    }
}
