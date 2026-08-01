<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BankTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankTransferPaymentController extends Controller
{
    public function show(Request $request, Order $order, BankTransferService $service): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);
        $payment = $order->payment;
        abort_unless($payment?->payment_method === 'bank_transfer', 404);
        $transfer = $service->createFor($payment)->load('slips');

        return response()->json(['data' => $this->payload($transfer)]);
    }

    public function upload(Request $request, Order $order, BankTransferService $service): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 403);
        $payment = $order->payment;
        abort_unless($payment?->payment_method === 'bank_transfer', 404);
        $validated = $request->validate(['slip' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:20480']]);
        $transfer = $service->uploadSlip($service->createFor($payment), $validated['slip'], $request->user());

        return response()->json(['message' => 'Payment slip uploaded for verification.', 'data' => $this->payload($transfer->load('slips'))], 201);
    }

    private function payload($transfer): array
    {
        return [
            'id' => $transfer->id,
            'order_id' => $transfer->order_id,
            'reference_number' => $transfer->reference_number,
            'expected_amount' => (float) $transfer->expected_amount,
            'currency' => $transfer->currency,
            'status' => $transfer->status,
            'last_rejection_reason' => $transfer->last_rejection_reason,
            'bank' => ['name' => $transfer->bank_name, 'account_holder' => $transfer->account_holder, 'account_number' => $transfer->account_number, 'branch' => $transfer->branch],
            'submitted_at' => $transfer->submitted_at,
            'verified_at' => $transfer->verified_at,
            'slips' => $transfer->slips->map(fn ($slip) => ['id' => $slip->id, 'original_name' => $slip->original_name, 'uploaded_at' => $slip->uploaded_at]),
        ];
    }
}
