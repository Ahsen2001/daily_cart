<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PayHereService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PayHereMobileController extends Controller
{
    public function checkout(Request $request, Order $order): JsonResponse
    {
        $this->ensureOrderOwned($request, $order);
        $payment = $order->payment()->firstOrFail();
        abort_unless($payment->payment_method === 'card', 422, 'This order is not configured for PayHere.');

        return response()->json([
            'payment_url' => URL::temporarySignedRoute(
                'api.v1.payhere.form',
                now()->addMinutes(15),
                ['payment' => $payment->id],
            ),
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ]);
    }

    public function form(
        Payment $payment,
        PayHereService $payHere,
        PaymentService $payments
    ) {
        abort_unless($payment->payment_method === 'card', 422);
        abort_if(in_array($payment->status, ['paid', 'refunded'], true), 422);
        $payment = $payments->syncPendingOrderAmounts($payment);
        $returnUrl = URL::temporarySignedRoute(
            'api.v1.payhere.return',
            now()->addHour(),
            ['payment' => $payment->id],
        );
        $cancelUrl = URL::temporarySignedRoute(
            'api.v1.payhere.cancel',
            now()->addHour(),
            ['payment' => $payment->id],
        );
        $payload = $payHere->checkoutPayload($payment, $returnUrl, $cancelUrl);
        $inputs = collect($payload)->map(
            fn ($value, $key) => '<input type="hidden" name="'.
                e((string) $key).'" value="'.e((string) $value).'">'
        )->implode('');

        return response(
            '<!doctype html><html><body><p>Opening secure payment…</p>'.
            '<form id="payhere" method="post" action="'.e($payHere->checkoutUrl()).'">'.$inputs.'</form>'.
            '<script>document.getElementById("payhere").submit();</script></body></html>'
        )->header('Content-Type', 'text/html');
    }

    public function return(Payment $payment)
    {
        return response('<!doctype html><html><body><h1>Payment submitted</h1></body></html>')
            ->header('Content-Type', 'text/html');
    }

    public function cancel(Payment $payment)
    {
        return response('<!doctype html><html><body><h1>Payment cancelled</h1></body></html>')
            ->header('Content-Type', 'text/html');
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        $this->ensureOrderOwned($request, $order);

        return response()->json([
            'order' => new OrderResource($order->refresh()->load('statusHistories')),
        ]);
    }

    private function ensureOrderOwned(Request $request, Order $order): void
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);
    }
}
