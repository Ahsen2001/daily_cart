<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessRefundRequest;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(Request $request): View
    {
        $refunds = Refund::query()
            ->with(['order.customer.user', 'order.vendor', 'payment'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }

    public function approve(ProcessRefundRequest $request, Refund $refund, RefundService $refunds): RedirectResponse
    {
        $refunds->approve($refund, $request->user(), $request->admin_note);

        return back()->with('status', 'Refund approved and credited to customer wallet.');
    }

    public function reject(ProcessRefundRequest $request, Refund $refund, RefundService $refunds): RedirectResponse
    {
        $refunds->reject($refund, $request->user(), $request->admin_note);

        return back()->with('status', 'Refund rejected.');
    }
}
