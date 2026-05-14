<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Models\Order;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(Request $request): View
    {
        $refunds = $request->user()->customer->orders()
            ->with('refunds.payment')
            ->whereHas('refunds')
            ->latest()
            ->paginate(15);

        return view('customer.refunds.index', compact('refunds'));
    }

    public function create(Order $order): View
    {
        $this->authorize('createForOrder', [Refund::class, $order]);

        return view('customer.refunds.create', [
            'order' => $order->load('payment'),
        ]);
    }

    public function store(StoreRefundRequest $request, Order $order, RefundService $refunds): RedirectResponse
    {
        $refunds->request($order, (float) $request->amount, $request->reason);

        return redirect()->route('customer.refunds.index')->with('status', 'Refund request submitted.');
    }
}
