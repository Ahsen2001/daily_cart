<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimulatePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Services\OrderStatusService;
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

        $order->load(['customer.user', 'vendor', 'items.product', 'payment', 'delivery']);

        if ($order->payment) {
            $payments->syncPendingOrderAmounts($order->payment);
            $order->refresh()->load(['customer.user', 'vendor', 'items.product', 'payment', 'delivery']);
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

        return redirect()
            ->route('customer.payments.show', $payment->order)
            ->with('status', 'Payment method updated.');
    }

    public function process(SimulatePaymentRequest $request, Payment $payment, PaymentService $payments, OrderStatusService $notifications): RedirectResponse
    {
        $payment = $payments->simulate($payment, $request->result === 'success')->load('order.customer.user');

        $notifications->notify(
            $payment->order->customer->user,
            $payment->status === 'paid'
                ? new PaymentSuccessNotification($payment)
                : new PaymentFailedNotification($payment)
        );

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
