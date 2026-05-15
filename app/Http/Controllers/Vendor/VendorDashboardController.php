<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService, AnalyticsService $analyticsService): View
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor?->status === 'approved', 403);

        return view('vendor.dashboard.index', [
            'summary' => $dashboardService->vendorOverview($vendor),
            'charts' => $analyticsService->vendorCharts($vendor->id, $request->only(['from', 'to', 'order_status'])),
        ]);
    }
}
