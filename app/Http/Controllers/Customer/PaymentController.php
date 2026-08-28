<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimulatePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\BankTransferService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Order $order, PaymentService $payments): View
    {
        $this->authorize('view', $order);

        $order->load(['customer.user', 'vendor', 'items.product', 'payment.bankTransferPayment', 'delivery']);

        if ($order->payment) {
            $payments->syncPendingOrderAmounts($order->payment);
            $order->refresh()->load(['customer.user', 'vendor', 'items.product', 'payment.bankTransferPayment', 'delivery']);
        }

        return view('customer.payments.show', [
            'order' => $order,
        ]);
    }

    public function updateMethod(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $this->authorize('view', $payment);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(PaymentService::METHODS)],
        ]);

        $payment = $payments->updateMethod($payment, $validated['payment_method']);

        if ($payment->payment_method === 'card') {
            return redirect()->route('customer.payments.payhere', $payment);
        }

        if ($payment->payment_method === 'bank_transfer') {
            return redirect()->route('customer.payments.bank-transfer', $payment);
        }

        return redirect()
            ->route('customer.payments.show', $payment->order)
            ->with('status', 'Payment method updated.');
    }

    public function bankTransfer(Payment $payment, BankTransferService $bankTransfers): View
    {
        $this->authorize('view', $payment);
        abort_unless($payment->payment_method === 'bank_transfer', 404);

        $transfer = $bankTransfers->createFor($payment)->load(['payment.order.customer.user', 'slips']);

        return view('customer.payments.bank-transfer', compact('payment', 'transfer'));
    }

    public function uploadBankTransferSlip(Request $request, Payment $payment, BankTransferService $bankTransfers): RedirectResponse
    {
        $this->authorize('view', $payment);
        abort_unless($payment->payment_method === 'bank_transfer', 404);
        $validated = $request->validate([
            'slip' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:20480'],
        ]);

        $transfer = $bankTransfers->createFor($payment);
        $bankTransfers->uploadSlip($transfer, $validated['slip'], $request->user());

        return redirect()->route('customer.payments.bank-transfer', $payment)
            ->with('status', 'Your payment slip was uploaded and is awaiting verification.');
    }

    public function process(SimulatePaymentRequest $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $payment = $payments->simulate($payment, $request->result === 'success')->load('order.customer.user');

        return redirect()->route(
            $payment->status === 'paid' ? 'customer.payments.success' : 'customer.payments.failed',
            $payment
        );
    }

    public function success(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('customer.payments.success', compact('payment'));
    }

    public function failed(Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('customer.payments.failed', compact('payment'));
    }
}
