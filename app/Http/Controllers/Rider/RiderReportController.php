<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $rider = $request->user()->rider;
        abort_unless(in_array($rider?->verification_status, ['verified', 'approved'], true), 403);

        return view('rider.reports.index', $reports->riderPrivate($rider, $request->only(['from', 'to'])));
    }
}
