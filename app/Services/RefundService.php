<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundRejectedNotification;
use App\Notifications\RefundRequestedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly PaymentService $payments,
        private readonly OrderStatusService $notifications,
        private readonly LoyaltyPointService $loyaltyPoints,
        private readonly ExternalEmailService $emails,
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

            $this->notifications->notify($order->customer->user, new RefundRequestedNotification($refund));
            $this->emails->refundStatus($refund->load('order.customer.user'), 'Your refund request has been received.');

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
            $this->notifications->notify($order->customer->user, new RefundApprovedNotification($refund));
            $this->emails->refundStatus($refund->load('order.customer.user'), 'Your refund request has been approved.');

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

            $this->notifications->notify($refund->order->customer->user, new RefundRejectedNotification($refund));
            $this->emails->refundStatus($refund->load('order.customer.user'), 'Your refund request has been rejected.');

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
