<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PayHereService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayHereController extends Controller
{
    public function checkout(Payment $payment, PayHereService $payHere, PaymentService $payments): View
    {
        $this->authorize('view', $payment);
        abort_unless($payment->payment_method === 'card', 403, 'PayHere checkout is available for card payments only.');
        abort_if(in_array($payment->status, ['paid', 'refunded'], true), 403, 'This payment has already been completed.');

        if ($payment->status !== 'pending') {
            $payment = $payments->updateMethod($payment, 'card');
        }

        $payment = $payments->syncPendingOrderAmounts($payment);

        return view('customer.payments.payhere', [
            'payment' => $payment->load(['order.customer.user', 'order.vendor', 'order.items.product']),
            'checkoutUrl' => $payHere->checkoutUrl(),
            'payload' => $payHere->checkoutPayload($payment),
        ]);
    }

    public function notify(Request $request, PayHereService $payHere, PaymentService $payments): string
    {
        return $payHere->processNotification($request->all(), $payments);
    }

    public function return(Payment $payment): RedirectResponse
    {
        return redirect()->route('customer.payments.success', $payment);
    }

    public function cancel(Payment $payment): RedirectResponse
    {
        return redirect()->route('customer.payments.failed', $payment);
    }
}
