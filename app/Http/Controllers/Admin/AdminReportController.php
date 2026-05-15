<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function sales(Request $request, ReportService $reports, ExportService $exports): View|StreamedResponse|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $reports->sales($this->filters($request));

        if ($response = $this->exportIfRequested($request, $exports, 'sales', $reports->summaryRows($data['summary']))) {
            return $response;
        }

        return view('admin.reports.sales', $data);
    }

    public function products(Request $request, ReportService $reports): View
    {
        $this->authorizeAdmin($request);

        return view('admin.reports.products', $reports->products($this->filters($request)));
    }

    public function vendors(Request $request, ReportService $reports): View
    {
        $this->authorizeAdmin($request);

        return view('admin.reports.vendors', $reports->vendors($this->filters($request)));
    }

    public function customers(Request $request, ReportService $reports): View
    {
        $this->authorizeAdmin($request);

        return view('admin.reports.customers', $reports->customers($this->filters($request)));
    }

    public function riders(Request $request, ReportService $reports): View
    {
        $this->authorizeAdmin($request);

        return view('admin.reports.riders', $reports->riders($this->filters($request)));
    }

    public function finance(Request $request, ReportService $reports, ExportService $exports): View|StreamedResponse|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $reports->finance($this->filters($request));

        if ($response = $this->exportIfRequested($request, $exports, 'finance', $reports->summaryRows($data['summary']))) {
            return $response;
        }

        return view('admin.reports.finance', $data);
    }

    public function support(Request $request, ReportService $reports, ExportService $exports): View|StreamedResponse|RedirectResponse
    {
        $this->authorizeAdmin($request);
        $data = $reports->support($this->filters($request));

        if ($response = $this->exportIfRequested($request, $exports, 'support', $reports->summaryRows($data['summary']))) {
            return $response;
        }

        return view('admin.reports.support', $data);
    }

    private function filters(Request $request): array
    {
        return $request->only([
            'from',
            'to',
            'vendor_id',
            'category_id',
            'customer_id',
            'rider_id',
            'order_status',
            'payment_method',
            'payment_status',
        ]);
    }

    private function exportIfRequested(Request $request, ExportService $exports, string $name, array $rows): StreamedResponse|RedirectResponse|null
    {
        if (! $request->filled('export')) {
            return null;
        }

        if ($request->string('export')->toString() === 'csv') {
            return $exports->csv('dailycart-'.$name.'-report.csv', ['Metric', 'Value'], $rows);
        }

        return back()->with('status', $exports->placeholder($request->string('export')->toString()));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->isAdminUser(), 403);
    }
}
