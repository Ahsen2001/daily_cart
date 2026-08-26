<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\ProductIdentityService;
use App\Services\VendorProductBulkImportService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorProductImportController extends Controller
{
    private const PREVIEW_SESSION_KEY = 'vendor_product_import_preview';

    public function create(): View
    {
        return view('vendor.products.import');
    }

    public function template(string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        if ($format === 'csv') {
            return response()->streamDownload(function (): void {
                $output = fopen('php://output', 'w');
                fputcsv($output, VendorProductBulkImportService::HEADERS);
                fclose($output);
            }, 'dailycart-product-import-template.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        return response()->streamDownload(function (): void {
            $spreadsheet = new Spreadsheet;
            $products = $spreadsheet->getActiveSheet()->setTitle('Products');
            $products->fromArray(VendorProductBulkImportService::HEADERS, null, 'A1');
            $products->getStyle('A1:K1')->getFont()->setBold(true);
            $products->freezePane('A2');

            foreach (range('A', 'K') as $column) {
                $products->getColumnDimension($column)->setAutoSize(true);
            }

            $instructions = $spreadsheet->createSheet()->setTitle('Instructions');
            $instructions->fromArray([
                ['DailyCart bulk product import'],
                ['Add one product per row on the Products sheet.'],
                ['Category must match an active DailyCart category name or slug.'],
                ['SKU and product URL slug are generated automatically. Do not add those columns.'],
                ['Product images are uploaded separately after a successful import.'],
                ['Every imported product is submitted for normal admin approval.'],
                ['Maximum 250 product rows per file. Formulas are not accepted.'],
            ], null, 'A1');
            $instructions->getColumnDimension('A')->setWidth(100);

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'dailycart-product-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function preview(Request $request, VendorProductBulkImportService $imports): View
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'],
        ]);

        $preview = $imports->preview($request->file('import_file'));
        $token = null;

        if ($preview['errors'] === []) {
            $token = Str::random(64);

            $request->session()->put(self::PREVIEW_SESSION_KEY, [
                'token' => $token,
                'vendor_id' => $request->user()->vendor->id,
                'user_id' => $request->user()->id,
                'expires_at' => now()->addMinutes(15)->getTimestamp(),
                'rows' => $preview['rows'],
            ]);
        }

        return view('vendor.products.import-preview', [
            'preview' => $preview,
            'token' => $token,
        ]);
    }

    public function confirm(
        Request $request,
        VendorProductBulkImportService $imports,
        ProductIdentityService $identity,
        NotificationService $notifications,
    ): RedirectResponse {
        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $stored = $request->session()->pull(self::PREVIEW_SESSION_KEY);

        if (
            ! is_array($stored)
            || ! hash_equals((string) ($stored['token'] ?? ''), $request->string('token')->toString())
            || ($stored['vendor_id'] ?? null) !== $request->user()->vendor->id
            || ($stored['user_id'] ?? null) !== $request->user()->id
            || ($stored['expires_at'] ?? 0) < now()->getTimestamp()
            || empty($stored['rows'])
        ) {
            throw ValidationException::withMessages([
                'import_file' => 'This import preview has expired. Upload the file again to validate it.',
            ]);
        }

        try {
            $products = $imports->import(
                $stored['rows'],
                $request->user()->vendor,
                $request->user(),
                $identity,
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->route('vendor.products.import.create')
                ->withErrors($exception->errors());
        } catch (QueryException) {
            return redirect()
                ->route('vendor.products.import.create')
                ->withErrors(['import_file' => 'The import could not be completed. Please review the file and try again.']);
        }

        $pendingProducts = collect($products)->where('status', 'pending');

        if ($pendingProducts->isNotEmpty()) {
            $notifications->notifyAdmins(
                'Bulk product approval required',
                sprintf(
                    '%s submitted %d product%s for approval.',
                    $request->user()->vendor->business_name,
                    $pendingProducts->count(),
                    Str::plural('s', $pendingProducts->count()),
                ),
                'bulk_product_submitted_for_approval',
                ['database', 'mail'],
                [
                    'vendor_id' => $request->user()->vendor->id,
                    'product_ids' => $pendingProducts->pluck('id')->all(),
                    'event_id' => 'bulk-product-import-'.Str::uuid(),
                ],
                '/admin/products?status=pending',
            );
        }

        return redirect()
            ->route('vendor.products.index')
            ->with('success', sprintf(
                '%d product%s imported successfully. They are awaiting normal admin approval.',
                count($products),
                Str::plural('s', count($products)),
            ));
    }
}
