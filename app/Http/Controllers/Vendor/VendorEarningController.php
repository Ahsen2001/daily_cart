<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EarningFilterRequest;
use App\Services\FinanceReportService;
use Illuminate\View\View;

class VendorEarningController extends Controller
{
    public function index(EarningFilterRequest $request, FinanceReportService $finance): View
    {
        return view('vendor.earnings.index', [
            'summary' => $finance->vendorSummary($request->user()->vendor, $request->from, $request->to),
        ]);
    }
}
