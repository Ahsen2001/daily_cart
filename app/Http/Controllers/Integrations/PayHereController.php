<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Services\ExternalEmailService;
use App\Services\OrderStatusService;
use App\Services\PayHereService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayHereController extends Controller
{
    public function checkout(Payment $payment, PayHereService $payHere): View
    {
        $this->authorize('process', $payment);
        abort_unless($payment->payment_method === 'card', 403, 'PayHere checkout is available for card payments only.');

        return view('customer.payments.payhere', [
            'payment' => $payment->load('order'),
            'checkoutUrl' => $payHere->checkoutUrl(),
            'payload' => $payHere->checkoutPayload($payment),
        ]);
    }

    public function notify(Request $request, PayHereService $payHere, PaymentService $payments, OrderStatusService $notifications, ExternalEmailService $emails): string
    {
        $payload = $request->all();

        if (! $payHere->verifyNotification($payload)) {
            return 'INVALID_SIGNATURE';
        }

        $order = Order::where('order_number', $payload['order_id'] ?? null)->with('payment', 'customer.user')->first();

        if (! $order || ! $order->payment) {
            return 'ORDER_NOT_FOUND';
        }

        DB::transaction(function () use ($order, $payload, $payHere, $payments, $notifications, $emails) {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            $transaction = $payHere->recordNotification($payment, $payload);

            if ((string) ($payload['status_code'] ?? '') === '2') {
                $payment = $payments->markPaid($payment, $payload['payment_id'] ?? null);
                $notifications->notify($order->customer->user, new PaymentSuccessNotification($payment));
            } else {
                $payment->update(['status' => 'failed']);
                $order->update(['payment_status' => 'failed']);
                $notifications->notify($order->customer->user, new PaymentFailedNotification($payment));
                $emails->paymentStatus($payment, 'Your DailyCart payment for order '.$order->order_number.' failed.');
            }

            $transaction->update(['payment_id' => $payment->id]);
        });

        return 'OK';
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
