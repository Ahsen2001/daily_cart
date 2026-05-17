<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Rider;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OutForDeliveryNotification;
use App\Notifications\RiderAssignedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function __construct(
        private readonly OrderStatusService $orderStatusService,
        private readonly LoyaltyPointService $loyaltyPointService,
    ) {}

    public function assignRider(Order $order, Rider $rider): Delivery
    {
        if ($order->order_status !== 'packed') {
            throw ValidationException::withMessages(['order_status' => 'Admin can assign rider only after order is packed.']);
        }

        if ($rider->verification_status !== 'verified') {
            throw ValidationException::withMessages(['rider_id' => 'Selected rider is not verified.']);
        }

        return DB::transaction(function () use ($order, $rider) {
            $delivery = $order->delivery()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'rider_id' => $rider->id,
                    'pickup_address' => $order->vendor->address,
                    'delivery_address' => $order->delivery_address,
                    'scheduled_at' => $order->scheduled_delivery_at,
                    'status' => 'assigned',
                ]
            );

            $order->update(['order_status' => 'assigned_to_rider']);
            $rider->update(['availability_status' => 'delivering']);

            $this->orderStatusService->notify($order->customer->user, new RiderAssignedNotification($order));
            $this->orderStatusService->notify($rider->user, new RiderAssignedNotification($order));

            return $delivery->refresh();
        });
    }

    public function accept(Delivery $delivery): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'assigned', 'Only assigned deliveries can be accepted.');

        $delivery->update(['accepted_at' => now()]);

        return $delivery->refresh();
    }

    public function markPickedUp(Delivery $delivery): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'assigned', 'Rider can mark picked up only assigned deliveries.');

        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);

            return $delivery->refresh();
        });
    }

    public function markOnTheWay(Delivery $delivery): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'picked_up', 'Rider can mark on the way only picked up deliveries.');

        return DB::transaction(function () use ($delivery) {
            $delivery->update(['status' => 'on_the_way']);
            $delivery->order->update(['order_status' => 'out_for_delivery']);
            $this->orderStatusService->notify($delivery->order->customer->user, new OutForDeliveryNotification($delivery->order));

            return $delivery->refresh();
        });
    }

    public function markDelivered(Delivery $delivery, UploadedFile $photo, ?UploadedFile $signature = null, ?string $note = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'on_the_way', 'Rider can mark delivered only on-the-way deliveries.');

        return DB::transaction(function () use ($delivery, $photo, $signature, $note) {
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $delivery->proofs()->create([
                'proof_image' => $photo->store('delivery-proofs', 'public'),
                'customer_signature' => $signature?->store('delivery-signatures', 'public'),
                'note' => $note,
                'submitted_at' => now(),
            ]);

            $order = $delivery->order;
            $order->update(['order_status' => 'delivered']);

            if ($order->payment?->payment_method === 'cash_on_delivery') {
                $order->payment()->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                $order->update(['payment_status' => 'paid']);
            }

            $delivery->rider?->update(['availability_status' => 'available']);
            $this->loyaltyPointService->earnForOrder($order->refresh());
            $this->orderStatusService->notify($order->customer->user, new OrderDeliveredNotification($order));
            app(ExternalEmailService::class)->orderStatus($order->loadMissing('customer.user'), 'Your order '.$order->order_number.' has been delivered. Your printable receipt is now available.');

            return $delivery->refresh();
        });
    }

    public function markFailed(Delivery $delivery, string $reason): Delivery
    {
        if (! in_array($delivery->status, ['assigned', 'picked_up', 'on_the_way'], true)) {
            throw ValidationException::withMessages(['status' => 'This delivery cannot be marked failed.']);
        }

        return DB::transaction(function () use ($delivery, $reason) {
            $delivery->update([
                'status' => 'failed',
                'failed_reason' => $reason,
            ]);
            $delivery->rider?->update(['availability_status' => 'available']);

            return $delivery->refresh();
        });
    }

    private function ensureDeliveryStatus(Delivery $delivery, string $expected, string $message): void
    {
        if ($delivery->status !== $expected) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }
}
