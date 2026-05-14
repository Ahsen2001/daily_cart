<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletTopUpRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;
        $this->authorize('view', $customer);

        return view('customer.wallet.index', [
            'customer' => $customer,
            'transactions' => $request->user()->walletTransactions()->latest()->limit(8)->get(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $customer = $request->user()->customer;
        $this->authorize('view', $customer);

        return view('customer.wallet.transactions', [
            'transactions' => $request->user()->walletTransactions()->latest()->paginate(20),
        ]);
    }

    public function topUp(WalletTopUpRequest $request, WalletService $wallets): RedirectResponse
    {
        $wallets->topUp($request->user()->customer, (float) $request->amount);

        return back()->with('status', 'Wallet top-up completed.');
    }
}
