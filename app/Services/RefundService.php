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
    ) {}

    public function request(Order $order, float $amount, string $reason): Refund
    {
        $payment = $order->payment;

        if (! $payment || $payment->status !== 'paid') {
            throw ValidationException::withMessages(['order' => 'Only paid orders can be refunded.']);
        }

        if (! in_array($order->order_status, ['delivered'], true)) {
            throw ValidationException::withMessages(['order' => 'Only delivered orders are eligible for refund requests.']);
        }

        $this->ensureRefundAmountIsValid($order, $amount);

        return DB::transaction(function () use ($order, $payment, $amount, $reason) {
            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'refund_method' => 'wallet',
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

        return DB::transaction(function () use ($refund, $adminNote) {
            $order = $refund->order()->lockForUpdate()->firstOrFail();
            $payment = $refund->payment()->lockForUpdate()->firstOrFail();

            $refund->update([
                'status' => 'approved',
                'admin_note' => $adminNote,
                'processed_at' => now(),
            ]);

            $this->wallets->refundToWallet(
                $order->customer,
                (float) $refund->amount,
                $order->order_number,
                'Refund approved for order '.$order->order_number
            );

            $this->payments->markRefunded($payment);
            $order->update(['order_status' => 'refunded']);
            $this->loyaltyPoints->reverseForOrder($order, 'Reversed because order was refunded.');
            $data = ['order_id' => $order->id, 'refund_id' => $refund->id, 'amount' => (float) $refund->amount, 'status' => 'approved'];
            $this->notifications->sendOnce(
                $order->customer->user,
                'Refund approved',
                CurrencyService::formatLkr($refund->amount).' was refunded to your wallet for order '.$order->order_number.'.',
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
                    'Order refund approved',
                    'The refund for order '.$order->order_number.' was approved.',
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

        return DB::transaction(function () use ($refund, $adminNote) {
            $refund->update([
                'status' => 'rejected',
                'admin_note' => $adminNote,
                'processed_at' => now(),
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
