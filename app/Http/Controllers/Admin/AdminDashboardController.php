<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService, AnalyticsService $analyticsService): View
    {
        abort_unless($request->user()->isAdminUser(), 403);

        return view('admin.dashboard', [
            'summary' => $dashboardService->adminOverview(),
            'charts' => $analyticsService->adminCharts($request->only(['from', 'to', 'vendor_id', 'customer_id', 'order_status'])),
        ]);
    }
}
