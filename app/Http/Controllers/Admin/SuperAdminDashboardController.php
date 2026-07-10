<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService, AnalyticsService $analyticsService): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        return view('admin.super_dashboard', [
            'summary' => $dashboardService->superAdminOverview(),
            'charts' => $analyticsService->adminCharts($request->only(['from', 'to', 'vendor_id', 'customer_id', 'order_status'])),
        ]);
    }
}
