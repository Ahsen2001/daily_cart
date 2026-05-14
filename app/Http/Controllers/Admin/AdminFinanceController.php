<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinanceFilterRequest;
use App\Services\FinanceReportService;
use Illuminate\View\View;

class AdminFinanceController extends Controller
{
    public function index(FinanceFilterRequest $request, FinanceReportService $finance): View
    {
        return view('admin.finance.index', [
            'summary' => $finance->adminSummary($request->from, $request->to),
        ]);
    }
}
