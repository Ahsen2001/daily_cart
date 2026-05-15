<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService): View
    {
        $rider = $request->user()->rider;
        abort_unless($rider?->verification_status === 'approved', 403);

        return view('rider.dashboard.index', [
            'summary' => $dashboardService->riderOverview($rider),
        ]);
    }
}
