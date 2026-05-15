<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsService $analyticsService, ReportService $reportService): View
    {
        abort_unless($request->user()->isAdminUser(), 403);

        $filters = $request->only(['from', 'to', 'vendor_id', 'customer_id', 'order_status']);

        return view('admin.analytics.index', [
            'charts' => $analyticsService->adminCharts($filters),
            'filters' => $reportService->filters(),
        ]);
    }
}
