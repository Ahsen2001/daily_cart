<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VendorProductBulkImportService
{
    public const HEADERS = ['name', 'category', 'brand', 'description', 'price', 'discount_price', 'unit_type', 'weight', 'barcode', 'stock_quantity', 'expiry_date'];

    private const MAX_ROWS = 250;

    /** @return array{rows: array<int, array<string, mixed>>, errors: array<int, array{line:int,messages:array<int,string>}>, total:int} */
    public function preview(UploadedFile $file): array
    {
        $source = $this->readRows($file);
        if (count($source) < 2) {
            throw ValidationException::withMessages(['import_file' => 'The file must contain a header row and at least one product row.']);
        }

        $headers = array_shift($source);
        $normalizedHeaders = array_map(fn ($value) => Str::of(preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $value)) ?? '')->lower()->replace([' ', '-'], '_')->toString(), $headers);
        if (count($headers) !== count(self::HEADERS) || $normalizedHeaders !== self::HEADERS) {
            throw ValidationException::withMessages(['import_file' => 'The header row does not match the DailyCart template. Download a fresh template; SKU, slug, and images must not be included.']);
        }

        $rows = collect($source)->map(fn ($values, $index) => ['line' => $index + 2, 'values' => $values])
            ->filter(fn ($row) => collect($row['values'])->contains(fn ($value) => trim((string) $value) !== ''))
            ->values()->all();
        if ($rows === []) {
            throw ValidationException::withMessages(['import_file' => 'Add at least one product row before previewing the import.']);
        }
        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages(['import_file' => 'A bulk import is limited to '.self::MAX_ROWS.' products.']);
        }

        $categories = Category::active()->get();
        $categoryLookup = [];
        foreach ($categories as $category) {
            $categoryLookup[Str::lower($category->name)] = $category;
            $categoryLookup[Str::lower($category->slug)] = $category;
        }
        $barcodes = collect($rows)->map(fn ($row) => trim((string) ($row['values'][8] ?? '')))->filter()->unique();
        $existingBarcodes = Product::withTrashed()->whereIn('barcode', $barcodes)->pluck('barcode')->all();

        $valid = [];
        $errors = [];
        $seenBarcodes = [];
        foreach ($rows as $sourceRow) {
            $values = array_pad($sourceRow['values'], count(self::HEADERS), null);
            $categoryName = trim((string) $values[1]);
            $category = $categoryLookup[Str::lower($categoryName)] ?? null;
            $row = [
                'line' => $sourceRow['line'], 'name' => trim((string) $values[0]), 'category' => $categoryName, 'category_id' => $category?->id,
                'brand' => $this->nullable($values[2]), 'description' => $this->nullable($values[3]), 'price' => $this->nullable($values[4]),
                'discount_price' => $this->nullable($values[5]), 'unit_type' => trim((string) $values[6]), 'weight' => $this->nullable($values[7]),
                'barcode' => $this->nullable($values[8]), 'stock_quantity' => $this->nullable($values[9]), 'expiry_date' => $this->nullable($values[10]),
            ];
            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'], 'category' => ['required', 'string', 'max:255'], 'brand' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'], 'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'], 'unit_type' => ['required', 'string', 'max:50'],
                'weight' => ['nullable', 'string', 'max:50'], 'barcode' => ['nullable', 'string', 'max:255'],
                'stock_quantity' => ['required', 'integer', 'min:0'], 'expiry_date' => ['nullable', 'date', 'after:today'],
            ]);
            $messages = $validator->errors()->all();
            if (! $row['category_id']) {
                $messages[] = 'Category must match an active DailyCart category name or slug.';
            }
            if ($row['barcode'] && in_array($row['barcode'], $existingBarcodes, true)) {
                $messages[] = 'Barcode already belongs to an existing product.';
            }
            if ($row['barcode'] && in_array($row['barcode'], $seenBarcodes, true)) {
                $messages[] = 'Barcode is duplicated in this file.';
            }
            if ($messages !== []) {
                $errors[] = ['line' => $row['line'], 'messages' => array_values(array_unique($messages))];

                continue;
            }
            if ($row['barcode']) {
                $seenBarcodes[] = $row['barcode'];
            }
            $row['row_number'] = $row['line'];
            $row['category_name'] = $category->name;
            $valid[] = $row;
        }

        return [
            'rows' => $valid,
            'errors' => array_map(
                fn (array $error) => [
                    'row' => $error['line'],
                    'message' => implode(' ', $error['messages']),
                ],
                $errors,
            ),
            'total' => count($rows),
        ];
    }

    /** @param array<int,array<string,mixed>> $rows @return array<int,Product> */
    public function import(array $rows, Vendor $vendor, User $user, ProductIdentityService $identity): array
    {
        return DB::transaction(function () use ($rows, $vendor, $user, $identity) {
            $products = [];
            foreach ($rows as $row) {
                if (! Category::active()->whereKey($row['category_id'])->exists()) {
                    throw ValidationException::withMessages(['import_file' => 'A selected category is no longer active. Preview the file again.']);
                }
                $stock = (int) $row['stock_quantity'];
                $product = Product::create([
                    'vendor_id' => $vendor->id, 'category_id' => $row['category_id'], 'created_by' => $user->id, 'name' => $row['name'],
                    'slug' => $identity->uniqueSlug($row['name']), 'brand' => $row['brand'], 'description' => $row['description'],
                    'price' => $row['price'], 'discount_price' => $row['discount_price'], 'base_price' => $row['price'], 'sale_price' => $row['discount_price'],
                    'unit_type' => $row['unit_type'], 'unit' => $row['unit_type'], 'weight' => $row['weight'], 'barcode' => $row['barcode'],
                    'stock_quantity' => $stock, 'expiry_date' => $row['expiry_date'], 'status' => $stock > 0 ? 'pending' : 'out_of_stock',
                ]);
                $product = $identity->assignSku($product);
                $product->inventory()->updateOrCreate(['product_variant_id' => null], ['quantity' => $stock, 'low_stock_threshold' => 5]);
                $products[] = $product;
            }

            return $products;
        });
    }

    /** @return array<int,array<int,mixed>> */
    private function readRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ['csv', 'txt'], true)) {
            $handle = fopen($file->getRealPath(), 'rb');
            $rows = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count($rows) > self::MAX_ROWS) {
                    fclose($handle);
                    throw ValidationException::withMessages(['import_file' => 'A bulk import is limited to '.self::MAX_ROWS.' products.']);
                }
                $rows[] = $row;
            }
            fclose($handle);

            return $rows;
        }
        if ($extension !== 'xlsx') {
            throw ValidationException::withMessages(['import_file' => 'Only CSV and XLSX files are supported.']);
        }
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
            if ($highestRow > self::MAX_ROWS + 1 || $highestColumn > count(self::HEADERS)) {
                throw ValidationException::withMessages(['import_file' => 'The Excel file has too many rows or unsupported columns.']);
            }
            $rows = [];
            for ($row = 1; $row <= $highestRow; $row++) {
                $values = [];
                for ($column = 1; $column <= count(self::HEADERS); $column++) {
                    $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($column).$row);
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        throw ValidationException::withMessages(['import_file' => 'Spreadsheet formulas are not allowed. Use plain values.']);
                    } $values[] = $cell->getValue();
                } $rows[] = $values;
            }
            $spreadsheet->disconnectWorksheets();

            return $rows;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable) {
            throw ValidationException::withMessages(['import_file' => 'We could not read this Excel file. Use the DailyCart template.']);
        }
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
