<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function overview(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 404);
        }

        if ($vendor->status !== 'approved') {
            return response()->json(['message' => 'Your vendor account is not approved yet.'], 403);
        }

        return response()->json([
            'summary' => $this->dashboardService->vendorOverview($vendor),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 404);
        }

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['customer.user', 'payment'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function wallet(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            return response()->json(['message' => 'Vendor profile not found.'], 404);
        }

        $wallet = $vendor->wallet;

        return response()->json([
            'wallet' => [
                'balance' => $wallet ? (float) $wallet->balance : 0.0,
                'pending_balance' => $wallet ? (float) $wallet->pending_balance : 0.0,
                'total_earned' => $wallet ? (float) $wallet->total_earned : 0.0,
                'total_withdrawn' => $wallet ? (float) $wallet->total_withdrawn : 0.0,
            ],
        ]);
    }
}
