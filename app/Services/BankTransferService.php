<?php

namespace App\Services;

use App\Models\BankTransferPayment;
use App\Models\FinancialVerificationLog;
use App\Models\Payment;
use App\Models\PaymentSlip;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BankTransferService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function createFor(Payment $payment): BankTransferPayment
    {
        if ($payment->payment_method !== 'bank_transfer') {
            throw ValidationException::withMessages(['payment_method' => 'This payment is not a bank transfer.']);
        }

        return DB::transaction(function () use ($payment) {
            $payment = Payment::query()->with('order')->lockForUpdate()->findOrFail($payment->id);
            $existing = $payment->bankTransferPayment()->first();
            if ($existing) {
                return $existing;
            }

            $details = $this->bankDetails();
            $transfer = BankTransferPayment::create([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'reference_number' => $this->reference($payment),
                'bank_name' => $details['bank_name'],
                'account_holder' => $details['account_holder'],
                'account_number' => $details['account_number'],
                'branch' => $details['branch'],
                'expected_amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => 'pending_upload',
            ]);

            $payment->update(['transaction_reference' => $transfer->reference_number]);
            $this->log($transfer, null, 'created', null, 'pending_upload');

            return $transfer;
        });
    }

    public function uploadSlip(BankTransferPayment $transfer, UploadedFile $file, User $customer): BankTransferPayment
    {
        if (! in_array($transfer->status, ['pending_upload', 'rejected'], true)) {
            throw ValidationException::withMessages(['slip' => 'A payment slip cannot be uploaded while this payment is being reviewed or is complete.']);
        }

        $filename = Str::uuid().'.'.strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'file');
        $path = 'bank-transfer-slips/'.now()->format('Y/m').'/'.$filename;
        Storage::disk('local')->putFileAs(dirname($path), $file, basename($path));

        try {
            $transfer = DB::transaction(function () use ($transfer, $customer, $file, $path) {
                $transfer = BankTransferPayment::query()->lockForUpdate()->findOrFail($transfer->id);
                if (! in_array($transfer->status, ['pending_upload', 'rejected'], true)) {
                    throw ValidationException::withMessages(['slip' => 'This payment has already been submitted for verification.']);
                }

                $from = $transfer->status;
                $slip = $transfer->slips()->create([
                    'uploaded_by' => $customer->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'uploaded_at' => now(),
                ]);

                $transfer->update([
                    'status' => 'pending_verification',
                    'submitted_at' => now(),
                    'last_rejection_reason' => null,
                    'rejected_at' => null,
                ]);
                $transfer->verifications()->create([
                    'payment_slip_id' => $slip->id,
                    'action' => 'slip_uploaded',
                    'status' => 'pending_verification',
                    'verified_at' => now(),
                ]);
                $this->log($transfer, $customer, 'slip_uploaded', $from, 'slip_uploaded', 'Payment slip uploaded.', ['payment_slip_id' => $slip->id]);
                $this->log($transfer, $customer, 'verification_requested', 'slip_uploaded', 'pending_verification');

                return $transfer->refresh()->load('payment.order.customer.user');
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $data = ['order_id' => $transfer->order_id, 'bank_transfer_payment_id' => $transfer->id];
        $this->notifications->sendOnce(
            $customer,
            'Payment slip uploaded',
            'Your payment slip for order '.$transfer->payment->order->order_number.' is awaiting verification.',
            'bank_transfer_slip_uploaded',
            ['database', 'mail', 'push'],
            $data,
            '/order-details/'.$transfer->order_id,
            'customer',
            'bank-transfer-slip-'.$transfer->id.'-'.$transfer->submitted_at?->timestamp,
        );
        $this->notifications->notifyAdmins(
            'Payment verification required',
            'A bank-transfer payment slip for order '.$transfer->payment->order->order_number.' requires review.',
            'bank_transfer_verification_required',
            ['database', 'mail', 'push'],
            $data,
            '/admin/bank-transfer-verifications',
        );

        return $transfer;
    }

    public function approve(BankTransferPayment $transfer, User $actor, ?string $notes = null): BankTransferPayment
    {
        $transfer = DB::transaction(function () use ($transfer, $actor, $notes) {
            $transfer = BankTransferPayment::query()->with(['payment.order.vendor.user', 'payment.order.customer.user'])->lockForUpdate()->findOrFail($transfer->id);
            if (! in_array($transfer->status, ['slip_uploaded', 'pending_verification'], true)) {
                throw ValidationException::withMessages(['payment' => 'Only submitted bank-transfer payments can be approved.']);
            }

            $from = $transfer->status;
            $slip = $transfer->slips()->latest('id')->first();
            $transfer->update(['status' => 'verified', 'verified_at' => now(), 'last_rejection_reason' => null]);
            $transfer->payment->update(['status' => 'paid', 'paid_at' => now(), 'transaction_reference' => $transfer->reference_number]);
            $transfer->payment->order->update(['payment_status' => 'paid']);
            $transfer->verifications()->create([
                'payment_slip_id' => $slip?->id,
                'verified_by' => $actor->id,
                'action' => 'approved',
                'status' => 'verified',
                'reason' => $notes,
                'verified_at' => now(),
            ]);
            $this->log($transfer, $actor, 'approved', $from, 'verified', $notes, ['payment_slip_id' => $slip?->id]);

            return $transfer->refresh()->load(['payment.order.vendor.user', 'payment.order.customer.user']);
        });

        $order = $transfer->payment->order;
        $data = ['order_id' => $order->id, 'bank_transfer_payment_id' => $transfer->id, 'status' => 'verified'];
        if ($order->customer?->user) {
            $this->notifications->sendOnce($order->customer->user, 'Payment approved', 'Your bank transfer for order '.$order->order_number.' has been verified.', 'bank_transfer_verified', ['database', 'mail', 'push'], $data, '/order-details/'.$order->id, 'customer', 'bank-transfer-approved-'.$transfer->id);
        }
        if ($order->vendor?->user) {
            $this->notifications->sendOnce($order->vendor->user, 'Payment verified', 'Order '.$order->order_number.' is paid and ready for your confirmation.', 'bank_transfer_verified', ['database', 'mail', 'push'], $data, '/vendor-order-details/'.$order->id, 'vendor', 'bank-transfer-vendor-verified-'.$transfer->id);
            $this->notifications->sendOnce($order->vendor->user, 'New order received', 'Order '.$order->order_number.' is ready for fulfilment.', 'new_order', ['database', 'mail', 'push'], $data, '/vendor-order-details/'.$order->id, 'vendor', 'bank-transfer-new-order-'.$transfer->id);
        }

        return $transfer;
    }

    public function reject(BankTransferPayment $transfer, User $actor, string $reason): BankTransferPayment
    {
        return $this->returnForReupload($transfer, $actor, $reason, 'rejected', 'rejected');
    }

    public function requestNewSlip(BankTransferPayment $transfer, User $actor, string $reason): BankTransferPayment
    {
        return $this->returnForReupload($transfer, $actor, $reason, 'pending_upload', 'reupload_requested');
    }

    public function bankDetails(): array
    {
        return [
            'bank_name' => config('services.bank_transfer.bank_name', 'DailyCart Bank Account'),
            'account_holder' => config('services.bank_transfer.account_holder', 'DailyCart (Pvt) Ltd'),
            'account_number' => config('services.bank_transfer.account_number', 'Configure in .env'),
            'branch' => config('services.bank_transfer.branch'),
        ];
    }

    private function returnForReupload(BankTransferPayment $transfer, User $actor, string $reason, string $status, string $action): BankTransferPayment
    {
        $transfer = DB::transaction(function () use ($transfer, $actor, $reason, $status, $action) {
            $transfer = BankTransferPayment::query()->with('payment.order.customer.user')->lockForUpdate()->findOrFail($transfer->id);
            if (! in_array($transfer->status, ['slip_uploaded', 'pending_verification'], true)) {
                throw ValidationException::withMessages(['payment' => 'Only submitted bank-transfer payments can be returned for another slip.']);
            }

            $from = $transfer->status;
            $transfer->update(['status' => $status, 'rejected_at' => now(), 'last_rejection_reason' => $reason]);
            $transfer->payment->update(['status' => 'pending', 'paid_at' => null]);
            $transfer->payment->order->update(['payment_status' => 'pending']);
            $transfer->verifications()->create(['verified_by' => $actor->id, 'action' => $action, 'status' => $status, 'reason' => $reason, 'verified_at' => now()]);
            $this->log($transfer, $actor, $action, $from, $status, $reason);

            return $transfer->refresh()->load('payment.order.customer.user');
        });

        $customer = $transfer->payment->order->customer?->user;
        if ($customer) {
            $data = ['order_id' => $transfer->order_id, 'bank_transfer_payment_id' => $transfer->id, 'status' => $transfer->status];
            $prefix = 'bank-transfer-'.$action.'-'.$transfer->id;
            $this->notifications->sendOnce($customer, 'Payment rejected', 'Your bank transfer for order '.$transfer->payment->order->order_number.' was not approved: '.$reason, 'bank_transfer_rejected', ['database', 'mail', 'push'], $data, '/order-details/'.$transfer->order_id, 'customer', $prefix.'-rejected');
            $this->notifications->sendOnce($customer, 'Payment slip required', 'Please upload a new payment slip for order '.$transfer->payment->order->order_number.'.', 'bank_transfer_reupload_required', ['database', 'mail', 'push'], $data, '/order-details/'.$transfer->order_id, 'customer', $prefix.'-reupload');
        }

        return $transfer;
    }

    private function reference(Payment $payment): string
    {
        do {
            $reference = 'DCP-'.now()->format('ymd').'-'.$payment->order_id.'-'.Str::upper(Str::random(5));
        } while (BankTransferPayment::query()->where('reference_number', $reference)->exists());

        return $reference;
    }

    private function log(BankTransferPayment $transfer, ?User $actor, string $action, ?string $from, ?string $to, ?string $notes = null, array $metadata = []): void
    {
        FinancialVerificationLog::create([
            'bank_transfer_payment_id' => $transfer->id,
            'payment_id' => $transfer->payment_id,
            'order_id' => $transfer->order_id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'metadata' => $metadata ?: null,
        ]);
    }
}
