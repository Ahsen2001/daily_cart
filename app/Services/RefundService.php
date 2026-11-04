<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly PaymentService $payments,
        private readonly NotificationService $notifications,
        private readonly LoyaltyPointService $loyaltyPoints,
        private readonly PayHereRefundService $payHereRefunds,
    ) {}

    public function request(Order $order, float $amount, string $reason): Refund
    {
        $payment = $order->payment;

        if (! $payment || ! in_array($payment->status, ['paid', 'partially_refunded'], true)) {
            throw ValidationException::withMessages(['order' => 'Only paid orders can be refunded.']);
        }

        if (! in_array($order->order_status, ['delivered'], true)) {
            throw ValidationException::withMessages(['order' => 'Only delivered orders are eligible for refund requests.']);
        }

        $this->ensureRefundAmountIsValid($order, $amount);

        return $this->createRequest($order, $payment, $amount, $reason);
    }

    public function requestForFailedDelivery(Order $order, float $amount, string $reason): Refund
    {
        $payment = $order->payment;
        if (! $payment || ! in_array($payment->status, ['paid', 'partially_refunded'], true)) {
            throw ValidationException::withMessages(['order' => 'Only paid failed deliveries can be refunded.']);
        }
        if ($order->delivery?->status !== 'failed') {
            throw ValidationException::withMessages(['order' => 'This order does not have a failed delivery.']);
        }
        $this->ensureRefundAmountIsValid($order, $amount);

        return $this->createRequest($order, $payment, $amount, $reason);
    }

    private function createRequest(Order $order, $payment, float $amount, string $reason): Refund
    {
        return DB::transaction(function () use ($order, $payment, $amount, $reason) {
            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'refund_method' => $payment->payment_method === 'card' ? 'payhere_gateway' : 'wallet',
                'refund_type' => round($amount, 2) >= round((float) $payment->amount, 2) ? 'full' : 'partial',
                'reason' => $reason,
                'status' => 'requested',
                'requested_at' => now(),
            ]);

            $data = ['order_id' => $order->id, 'refund_id' => $refund->id, 'amount' => (float) $refund->amount, 'status' => 'requested'];
            $this->notifications->sendOnce(
                $order->customer->user,
                'Refund request received',
                'Your refund request for order '.$order->order_number.' is awaiting review.',
                'refund_requested',
                ['database', 'mail', 'push'],
                $data,
                '/customer/refunds',
                'customer',
                'refund-requested-customer-'.$refund->id,
            );
            if ($order->vendor?->user) {
                $this->notifications->sendOnce(
                    $order->vendor->user,
                    'Customer refund requested',
                    'A refund was requested for order '.$order->order_number.'.',
                    'refund_requested',
                    ['database', 'mail', 'push'],
                    $data,
                    '/vendor/refunds',
                    'vendor',
                    'refund-requested-vendor-'.$refund->id,
                );
            }
            $this->notifications->notifyAdmins(
                'Refund review required',
                'A refund was requested for order '.$order->order_number.'.',
                'refund_review_required',
                ['database', 'mail', 'push'],
                $data,
                '/admin/refunds',
            );

            return $refund->refresh();
        });
    }

    public function approve(Refund $refund, User $admin, ?string $adminNote = null): Refund
    {
        if ($refund->status !== 'requested') {
            throw ValidationException::withMessages(['refund' => 'Only requested refunds can be approved.']);
        }

        $this->ensureRefundAmountIsValid($refund->order, (float) $refund->amount, $refund);

        $refund->loadMissing('payment');
        $resolvedMethod = $refund->payment?->payment_method === 'card' ? 'payhere_gateway' : 'wallet';
        if ($refund->refund_method !== $resolvedMethod) {
            $refund->update(['refund_method' => $resolvedMethod]);
        }

        $gatewayResult = $resolvedMethod === 'payhere_gateway'
            ? $this->payHereRefunds->refund($refund)
            : null;

        return DB::transaction(function () use ($refund, $admin, $adminNote, $gatewayResult) {
            $refund = Refund::query()->lockForUpdate()->findOrFail($refund->id);
            if ($refund->status !== 'requested') {
                return $refund->refresh();
            }
            $order = $refund->order()->lockForUpdate()->firstOrFail();
            $payment = $refund->payment()->lockForUpdate()->firstOrFail();

            $alreadyRefunded = (float) $order->refunds()
                ->whereIn('status', ['approved', 'processed'])
                ->whereKeyNot($refund->id)
                ->sum('amount');
            $cumulativeRefunded = round($alreadyRefunded + (float) $refund->amount, 2);
            $isFullRefund = $cumulativeRefunded >= round((float) $payment->amount, 2);

            $refund->update([
                'status' => 'processed',
                'admin_note' => $adminNote,
                'processed_at' => now(),
                'processed_by' => $admin->id,
                'gateway_refund_id' => $gatewayResult['gateway_refund_id'] ?? null,
                'failure_reason' => null,
            ]);

            if ($refund->refund_method === 'wallet') {
                $this->wallets->refundToWallet(
                    $order->customer,
                    (float) $refund->amount,
                    $order->order_number.'-REF-'.$refund->id,
                    'Refund processed for order '.$order->order_number
                );
            }

            $this->payments->applyRefund($payment, (float) $refund->amount);
            if ($isFullRefund) {
                $order->update(['order_status' => 'refunded']);
            }
            $this->loyaltyPoints->reverseForRefund($order, $cumulativeRefunded, (float) $payment->amount);
            $data = [
                'order_id' => $order->id, 'refund_id' => $refund->id,
                'amount' => (float) $refund->amount, 'status' => 'processed',
                'refund_type' => $isFullRefund ? 'full' : 'partial',
                'refund_method' => $refund->refund_method,
            ];
            $this->notifications->sendOnce(
                $order->customer->user,
                $isFullRefund ? 'Refund processed' : 'Partial refund processed',
                CurrencyService::formatLkr($refund->amount).' was refunded '.($refund->refund_method === 'payhere_gateway' ? 'through PayHere' : 'to your wallet').' for order '.$order->order_number.'.',
                'refund_approved',
                ['database', 'mail', 'push'],
                $data,
                '/customer/refunds',
                'customer',
                'refund-approved-customer-'.$refund->id,
            );
            if ($order->vendor?->user) {
                $this->notifications->sendOnce(
                    $order->vendor->user,
                    $isFullRefund ? 'Order refund processed' : 'Partial order refund processed',
                    CurrencyService::formatLkr($refund->amount).' was refunded for order '.$order->order_number.'.',
                    'refund_approved',
                    ['database', 'mail', 'push'],
                    $data,
                    '/vendor/refunds',
                    'vendor',
                    'refund-approved-vendor-'.$refund->id,
                );
            }

            return $refund->refresh();
        });
    }

    public function reject(Refund $refund, User $admin, ?string $adminNote = null): Refund
    {
        if ($refund->status !== 'requested') {
            throw ValidationException::withMessages(['refund' => 'Only requested refunds can be rejected.']);
        }

        return DB::transaction(function () use ($refund, $admin, $adminNote) {
            $refund->update([
                'status' => 'rejected',
                'admin_note' => $adminNote,
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);

            $order = $refund->order;
            $data = ['order_id' => $order->id, 'refund_id' => $refund->id, 'amount' => (float) $refund->amount, 'status' => 'rejected'];
            $this->notifications->sendOnce(
                $order->customer->user,
                'Refund request rejected',
                'Your refund request for order '.$order->order_number.' was rejected.'.($adminNote ? ' Reason: '.$adminNote : ''),
                'refund_rejected',
                ['database', 'mail', 'push'],
                $data,
                '/customer/refunds',
                'customer',
                'refund-rejected-customer-'.$refund->id,
            );
            if ($order->vendor?->user) {
                $this->notifications->sendOnce(
                    $order->vendor->user,
                    'Order refund rejected',
                    'The refund request for order '.$order->order_number.' was rejected.',
                    'refund_rejected',
                    ['database', 'push'],
                    $data,
                    '/vendor/refunds',
                    'vendor',
                    'refund-rejected-vendor-'.$refund->id,
                );
            }

            return $refund->refresh();
        });
    }

    private function ensureRefundAmountIsValid(Order $order, float $amount, ?Refund $currentRefund = null): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Refund amount must be greater than zero.']);
        }

        $paidAmount = (float) $order->payment?->amount;
        $approvedRefunds = (float) $order->refunds()
            ->whereIn('status', ['approved', 'processed'])
            ->when($currentRefund, fn ($query) => $query->whereKeyNot($currentRefund->id))
            ->sum('amount');

        if ($amount > ($paidAmount - $approvedRefunds)) {
            throw ValidationException::withMessages(['amount' => 'Refund amount cannot exceed the paid amount.']);
        }
    }
}
