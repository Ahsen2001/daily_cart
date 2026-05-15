<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor?->status === 'approved', 403);

        return view('vendor.reports.index', $reports->vendorPrivate($vendor, $request->only(['from', 'to', 'order_status'])));
    }
}
