<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function excel(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet()->setTitle('Report');

            foreach (array_values($headers) as $column => $header) {
                $sheet->setCellValueExplicit([$column + 1, 1], (string) $header, DataType::TYPE_STRING);
            }

            $rowNumber = 2;
            foreach ($rows as $row) {
                foreach (array_values($row) as $column => $value) {
                    $sheet->setCellValueExplicit([$column + 1, $rowNumber], (string) $value, DataType::TYPE_STRING);
                }
                $rowNumber++;
            }

            $lastColumn = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
            $sheet->setAutoFilter("A1:{$lastColumn}1");
            $sheet->freezePane('A2');

            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pdf(string $filename, string $title, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($title, $headers, $rows): void {
            $lines = [$title, 'Generated: '.now()->format('Y-m-d H:i:s'), ''];
            $lines[] = implode(' | ', array_map('strval', $headers));
            $lines[] = str_repeat('-', 90);

            foreach ($rows as $row) {
                $lines[] = implode(' | ', array_map('strval', array_values($row)));
            }

            echo $this->buildPdf($lines);
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function buildPdf(array $lines): string
    {
        $pages = array_chunk($lines, 45);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>',
        ];
        $pageReferences = [];
        $nextObject = 4;

        foreach ($pages as $pageLines) {
            $pageObject = $nextObject++;
            $contentObject = $nextObject++;
            $pageReferences[] = $pageObject.' 0 R';

            $commands = "BT\n/F1 10 Tf\n50 790 Td\n16 TL\n";
            foreach ($pageLines as $line) {
                $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', (string) $line);
                $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded ?: '');
                $commands .= '('.$escaped.") Tj\nT*\n";
            }
            $commands .= "ET\n";

            $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                .'/Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObject.' 0 R >>';
            $objects[$contentObject] = '<< /Length '.strlen($commands).">>\nstream\n{$commands}endstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageReferences).'] /Count '.count($pageReferences).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($number = 1; $number <= count($objects); $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        return $pdf.'trailer'."\n<< /Size ".(count($objects) + 1).' /Root 1 0 R >>'."\n"
            .'startxref'."\n{$xrefOffset}\n%%EOF";
    }
}
