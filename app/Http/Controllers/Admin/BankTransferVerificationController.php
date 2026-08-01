<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransferPayment;
use App\Models\PaymentSlip;
use App\Services\BankTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BankTransferVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $payments = BankTransferPayment::query()
            ->with(['payment.order.customer.user', 'payment.order.vendor', 'slips'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bank-transfer-verifications.index', compact('payments', 'status'));
    }

    public function approve(Request $request, BankTransferPayment $bankTransferPayment, BankTransferService $service): RedirectResponse
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);
        $service->approve($bankTransferPayment, $request->user(), $data['notes'] ?? null);

        return back()->with('status', 'Bank transfer approved. The vendor has been notified.');
    }

    public function reject(Request $request, BankTransferPayment $bankTransferPayment, BankTransferService $service): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $service->reject($bankTransferPayment, $request->user(), $data['reason']);

        return back()->with('status', 'Payment rejected and the customer was asked to upload a new slip.');
    }

    public function requestNewSlip(Request $request, BankTransferPayment $bankTransferPayment, BankTransferService $service): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $service->requestNewSlip($bankTransferPayment, $request->user(), $data['reason']);

        return back()->with('status', 'A new payment slip was requested from the customer.');
    }

    public function downloadSlip(PaymentSlip $slip)
    {
        abort_unless(Storage::disk($slip->disk)->exists($slip->path), 404);

        return Storage::disk($slip->disk)->download($slip->path, $slip->original_name);
    }
}
