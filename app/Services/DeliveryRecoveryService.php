<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryRecoveryCase;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryRecoveryService
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly RefundService $refunds,
    ) {}

    public function open(Delivery $delivery, string $category, string $details, ?User $actor): DeliveryRecoveryCase
    {
        if (! array_key_exists($category, config('delivery_recovery.reason_categories', []))) {
            throw ValidationException::withMessages(['failed_reason_category' => 'Select a valid failure category.']);
        }

        return DB::transaction(function () use ($delivery, $category, $details, $actor) {
            $locked = Delivery::query()->lockForUpdate()->findOrFail($delivery->id);
            $sequence = ((int) $locked->recoveryCases()->max('failure_sequence')) + 1;
            $case = $locked->recoveryCases()->create([
                'order_id' => $locked->order_id,
                'failure_sequence' => $sequence,
                'reason_category' => $category,
                'reason_details' => $details,
                'status' => 'open',
                'failed_by' => $actor?->id,
                'escalation_due_at' => now()->addMinutes(max(1, (int) config('delivery_recovery.escalation_sla_minutes', 30))),
            ]);
            $this->event($case, 'failure_reported', null, 'open', $actor, $details, ['reason_category' => $category]);
            $this->notify($case, 'Delivery recovery required', 'A failed delivery requires an operational decision.', 'delivery_recovery_opened');

            return $case;
        });
    }

    public function retry(DeliveryRecoveryCase $case, User $admin, ?string $note = null): DeliveryRecoveryCase
    {
        return DB::transaction(function () use ($case, $admin, $note) {
            $case = $this->lockActionable($case);
            $delivery = $case->delivery()->lockForUpdate()->firstOrFail();
            if (! $delivery->rider || $delivery->rider->verification_status !== 'verified') {
                throw ValidationException::withMessages(['rider' => 'The previous rider is not available for retry. Reassign another rider.']);
            }
            $this->resumeDelivery($delivery, $delivery->rider);
            $this->resolve($case, 'retry_started', $admin, $note);
            $this->notify($case, 'Delivery retry started', 'The failed delivery will be retried by the assigned rider.', 'delivery_recovery_retry');

            return $case->refresh();
        });
    }

    public function reassign(DeliveryRecoveryCase $case, Rider $rider, User $admin, ?string $note = null): DeliveryRecoveryCase
    {
        if ($rider->verification_status !== 'verified') {
            throw ValidationException::withMessages(['rider_id' => 'Select a verified rider.']);
        }

        return DB::transaction(function () use ($case, $rider, $admin, $note) {
            $case = $this->lockActionable($case);
            $delivery = $case->delivery()->lockForUpdate()->firstOrFail();
            $previous = $delivery->rider;
            $previous?->update(['availability_status' => 'available']);
            $this->resumeDelivery($delivery, $rider);
            $this->resolve($case, 'reassigned', $admin, $note, ['previous_rider_id' => $previous?->id, 'rider_id' => $rider->id]);
            $this->notify($case, 'Delivery reassigned', 'The failed delivery was assigned to another rider.', 'delivery_recovery_reassigned');

            return $case->refresh();
        });
    }

    public function startReturn(DeliveryRecoveryCase $case, User $admin, ?string $note = null): DeliveryRecoveryCase
    {
        return DB::transaction(function () use ($case, $admin, $note) {
            $case = $this->lockActionable($case);
            $from = $case->status;
            $case->update(['status' => 'return_to_vendor', 'return_started_at' => now(), 'assigned_admin_id' => $admin->id]);
            $case->delivery?->rider?->update(['availability_status' => 'delivering']);
            $this->event($case, 'return_started', $from, 'return_to_vendor', $admin, $note);
            $this->notify($case, 'Return to vendor started', 'The failed delivery is being returned to the vendor.', 'delivery_return_started');

            return $case->refresh();
        });
    }

    public function completeReturn(DeliveryRecoveryCase $case, User $admin, ?string $note = null): DeliveryRecoveryCase
    {
        return DB::transaction(function () use ($case, $admin, $note) {
            $case = DeliveryRecoveryCase::query()->lockForUpdate()->findOrFail($case->id);
            if ($case->status !== 'return_to_vendor') {
                throw ValidationException::withMessages(['recovery' => 'Only an active return can be completed.']);
            }
            $case->delivery?->rider?->update(['availability_status' => 'available']);
            $case->order()->update(['order_status' => 'cancelled']);
            $paid = $case->order?->payment?->status === 'paid';
            $to = $paid ? 'refund_pending' : 'resolved';
            $updates = ['status' => $to, 'return_completed_at' => now(), 'assigned_admin_id' => $admin->id];
            if ($paid) {
                $updates['refund_decision'] = 'pending';
            } else {
                $updates += ['refund_decision' => 'not_required', 'resolution' => 'returned_to_vendor', 'resolved_at' => now(), 'resolved_by' => $admin->id, 'resolution_notes' => $note];
            }
            $case->update($updates);
            $this->event($case, 'return_completed', 'return_to_vendor', $to, $admin, $note, ['refund_required' => $paid]);
            $this->notify($case, 'Return to vendor completed', $paid ? 'The return is complete and needs a refund decision.' : 'The return is complete.', 'delivery_return_completed');

            return $case->refresh();
        });
    }

    public function approveRefund(DeliveryRecoveryCase $case, User $admin, float $amount, ?string $note = null): DeliveryRecoveryCase
    {
        return DB::transaction(function () use ($case, $admin, $amount, $note) {
            $case = $this->lockActionable($case);
            $refund = $this->refunds->requestForFailedDelivery($case->order, $amount, $case->reason_details);
            $this->refunds->approve($refund, $admin, $note);
            $case->update(['refund_id' => $refund->id, 'refund_decision' => 'approved']);
            $this->resolve($case, 'refund_approved', $admin, $note, ['refund_id' => $refund->id, 'amount' => $amount]);

            return $case->refresh();
        });
    }

    public function rejectRefund(DeliveryRecoveryCase $case, User $admin, string $note): DeliveryRecoveryCase
    {
        return DB::transaction(function () use ($case, $admin, $note) {
            $case = $this->lockActionable($case);
            $case->update(['refund_decision' => 'rejected']);
            $this->resolve($case, 'refund_rejected', $admin, $note);
            $this->notify($case, 'Refund decision recorded', 'The refund for the failed delivery was not approved. '.$note, 'delivery_refund_rejected');

            return $case->refresh();
        });
    }

    public function escalateOverdue(): int
    {
        $count = 0;
        DeliveryRecoveryCase::query()->where('status', '!=', 'resolved')->whereNull('escalated_at')
            ->where('escalation_due_at', '<=', now())->orderBy('id')->chunkById(100, function ($cases) use (&$count) {
                foreach ($cases as $case) {
                    DB::transaction(function () use ($case, &$count) {
                        $locked = DeliveryRecoveryCase::query()->lockForUpdate()->find($case->id);
                        if (! $locked || $locked->isResolved() || $locked->escalated_at) return;
                        $locked->update(['escalated_at' => now()]);
                        $this->event($locked, 'sla_escalated', $locked->status, $locked->status, null, 'Recovery SLA exceeded.');
                        $this->notifications->notifyAdmins('Failed delivery SLA exceeded', 'Recovery case #'.$locked->id.' requires immediate attention.', 'delivery_recovery_escalated', ['database', 'mail', 'push'], $this->data($locked), '/admin/delivery-recoveries/'.$locked->id);
                        $count++;
                    });
                }
            });

        return $count;
    }

    private function resumeDelivery(Delivery $delivery, Rider $rider): void
    {
        $delivery->update([
            'rider_id' => $rider->id, 'status' => 'assigned', 'accepted_at' => null,
            'picked_up_at' => null, 'delivered_at' => null, 'failed_reason' => null,
            'failed_reason_category' => null,
        ]);
        $delivery->order()->update(['order_status' => 'assigned_to_rider']);
        $rider->update(['availability_status' => 'delivering']);
    }

    private function lockActionable(DeliveryRecoveryCase $case): DeliveryRecoveryCase
    {
        $case = DeliveryRecoveryCase::query()->with(['delivery.rider', 'order.payment'])->lockForUpdate()->findOrFail($case->id);
        if ($case->isResolved()) throw ValidationException::withMessages(['recovery' => 'This recovery case is already resolved.']);
        if ($case->delivery?->status !== 'failed') throw ValidationException::withMessages(['delivery' => 'The delivery is no longer failed.']);

        return $case;
    }

    private function resolve(DeliveryRecoveryCase $case, string $resolution, User $admin, ?string $note, array $metadata = []): void
    {
        $from = $case->status;
        $case->update(['status' => 'resolved', 'resolution' => $resolution, 'resolved_at' => now(), 'resolved_by' => $admin->id, 'assigned_admin_id' => $admin->id, 'resolution_notes' => $note]);
        $this->event($case, $resolution, $from, 'resolved', $admin, $note, $metadata);
    }

    private function event(DeliveryRecoveryCase $case, string $type, ?string $from, ?string $to, ?User $actor, ?string $note = null, array $metadata = []): void
    {
        $case->events()->create(['actor_id' => $actor?->id, 'event_type' => $type, 'from_status' => $from, 'to_status' => $to, 'note' => $note, 'metadata' => $metadata ?: null]);
    }

    private function data(DeliveryRecoveryCase $case): array
    {
        return ['recovery_case_id' => $case->id, 'delivery_id' => $case->delivery_id, 'order_id' => $case->order_id, 'status' => $case->status];
    }

    private function notify(DeliveryRecoveryCase $case, string $title, string $message, string $type): void
    {
        $case->loadMissing(['order.customer.user', 'order.vendor.user', 'delivery.rider.user']);
        $data = $this->data($case);
        $event = $type.'-'.$case->id.'-'.$case->events()->count();
        if ($case->order?->customer?->user) $this->notifications->sendOnce($case->order->customer->user, $title, $message, $type, ['database', 'mail', 'push'], $data, '/customer/orders/'.$case->order_id, 'customer', $event.'-customer');
        if ($case->order?->vendor?->user) $this->notifications->sendOnce($case->order->vendor->user, $title, $message, $type, ['database', 'push'], $data, '/vendor/orders/'.$case->order_id, 'vendor', $event.'-vendor');
        if ($case->delivery?->rider?->user) $this->notifications->sendOnce($case->delivery->rider->user, $title, $message, $type, ['database', 'push'], $data, '/assigned-deliveries', 'rider', $event.'-rider');
        $this->notifications->notifyAdmins($title, $message, $type, ['database', 'push'], $data, '/admin/delivery-recoveries/'.$case->id);
    }
}
