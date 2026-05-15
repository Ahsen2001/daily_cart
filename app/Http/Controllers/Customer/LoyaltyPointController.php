<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyPointService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyPointController extends Controller
{
    public function index(Request $request, LoyaltyPointService $loyalty): View
    {
        $customer = $request->user()->customer;
        $this->authorize('view', $customer);

        return view('customer.loyalty.index', [
            'balance' => $loyalty->balance($customer),
            'setting' => $loyalty->setting(),
            'transactions' => $customer->loyaltyPoints()->latest()->paginate(20),
        ]);
    }
}
