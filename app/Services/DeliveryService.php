<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function __construct(
        private readonly OrderStatusService $orderStatusService,
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly ExternalEmailService $emails,
        private readonly FinancialPolicyService $financialPolicy,
        private readonly OrderUpdateNotificationService $orderUpdates,
    ) {}

    public function assignRider(Order $order, Rider $rider, ?User $actor = null): Delivery
    {
        if ($order->order_status !== 'packed') {
            throw ValidationException::withMessages(['order_status' => 'Admin can assign rider only after order is packed.']);
        }

        if ($rider->verification_status !== 'verified') {
            throw ValidationException::withMessages(['rider_id' => 'Selected rider is not verified.']);
        }

        return DB::transaction(function () use ($order, $rider, $actor) {
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
            $order->load(['delivery.rider.user', 'customer.user']);

            $this->orderUpdates->riderAssigned($order, $actor);

            return $delivery->refresh();
        });
    }

    public function accept(Delivery $delivery, ?User $actor = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'assigned', 'Only assigned deliveries can be accepted.');

        $delivery->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
        $this->orderUpdates->statusChanged($delivery->order, 'delivery_accepted', $actor);

        return $delivery->refresh();
    }

    public function markPickedUp(Delivery $delivery, ?User $actor = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'accepted', 'Rider must accept the delivery before pickup.');

        return DB::transaction(function () use ($delivery, $actor) {
            $delivery->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);
            $this->orderUpdates->statusChanged($delivery->order, 'picked_up', $actor);

            return $delivery->refresh();
        });
    }

    public function markOnTheWay(Delivery $delivery, ?User $actor = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'picked_up', 'Rider can mark on the way only picked up deliveries.');

        return DB::transaction(function () use ($delivery, $actor) {
            $delivery->update(['status' => 'on_the_way']);
            $delivery->order->update(['order_status' => 'out_for_delivery']);
            $this->orderUpdates->statusChanged($delivery->order, 'out_for_delivery', $actor);

            return $delivery->refresh();
        });
    }

    public function markDelivered(Delivery $delivery, UploadedFile $photo, ?UploadedFile $signature = null, ?string $note = null, ?User $actor = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'on_the_way', 'Rider can mark delivered only on-the-way deliveries.');
        $proofImagePath = $this->storeProofFile($photo, 'delivery-proofs', 'proof_image');
        $signaturePath = $signature ? $this->storeProofFile($signature, 'delivery-signatures', 'customer_signature') : null;

        return DB::transaction(function () use ($delivery, $proofImagePath, $signaturePath, $note, $actor) {
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            $delivery->loadMissing('order');
            $delivery->update(['rider_payout' => $this->financialPolicy->riderPayout($delivery)]);

            $delivery->proofs()->create([
                'proof_image' => $proofImagePath,
                'customer_signature' => $signaturePath,
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
            $this->orderUpdates->statusChanged($order, 'delivered', $actor);
            $this->emails->outForDeliveryInvoice($order->loadMissing([
                'customer.user',
                'vendor',
                'items',
                'payment',
                'delivery.rider.user',
            ]));

            return $delivery->refresh();
        });
    }

    public function replaceProof(Delivery $delivery, UploadedFile $photo, ?UploadedFile $signature = null, ?string $note = null): Delivery
    {
        $this->ensureDeliveryStatus($delivery, 'delivered', 'Delivery proof can be replaced only after delivery is completed.');
        $proofImagePath = $this->storeProofFile($photo, 'delivery-proofs', 'proof_image');
        $signaturePath = $signature ? $this->storeProofFile($signature, 'delivery-signatures', 'customer_signature') : null;

        DB::transaction(function () use ($delivery, $proofImagePath, $signaturePath, $note) {
            $proof = $delivery->proofs()->latest('id')->first();

            if ($proof) {
                $proof->update([
                    'proof_image' => $proofImagePath,
                    'customer_signature' => $signaturePath ?? $proof->customer_signature,
                    'note' => $note ?? $proof->note,
                    'submitted_at' => now(),
                ]);

                return;
            }

            $delivery->proofs()->create([
                'proof_image' => $proofImagePath,
                'customer_signature' => $signaturePath,
                'note' => $note,
                'submitted_at' => now(),
            ]);
        });

        return $delivery->refresh();
    }

    public function markFailed(Delivery $delivery, string $reason, ?User $actor = null): Delivery
    {
        if (! in_array($delivery->status, ['assigned', 'picked_up', 'on_the_way'], true)) {
            throw ValidationException::withMessages(['status' => 'This delivery cannot be marked failed.']);
        }

        return DB::transaction(function () use ($delivery, $reason, $actor) {
            $delivery->update([
                'status' => 'failed',
                'failed_reason' => $reason,
            ]);
            $delivery->rider?->update(['availability_status' => 'available']);
            $this->orderUpdates->statusChanged($delivery->order, 'delivery_failed', $actor);

            return $delivery->refresh();
        });
    }

    private function ensureDeliveryStatus(Delivery $delivery, string $expected, string $message): void
    {
        if ($delivery->status !== $expected) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function storeProofFile(UploadedFile $file, string $directory, string $field): string
    {
        try {
            $targetDirectory = storage_path('app/public/'.$directory);
            File::ensureDirectoryExists($targetDirectory);

            $extension = $file->extension() ?: $file->getClientOriginalExtension() ?: 'jpg';
            $filename = Str::uuid().'.'.strtolower($extension);
            $file->move($targetDirectory, $filename);

            if (! File::isFile($targetDirectory.DIRECTORY_SEPARATOR.$filename)) {
                throw new \RuntimeException('Uploaded proof file was not found after the move.');
            }

            return trim($directory, '/').'/'.$filename;
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $field => 'The image could not be saved. Please choose the image again and retry.',
            ]);
        }
    }
}
