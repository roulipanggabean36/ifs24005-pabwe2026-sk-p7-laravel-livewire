<?php

namespace App\Features\Stock\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockExcelService
{
    public const HEADERS = ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status'];

    public function __construct(private readonly StockService $stockService)
    {
    }

    /** Hasilkan isi file .xlsx (binary) dari kumpulan stok. */
    public function export(Collection $stocks): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stok');
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->fromArray([
                $stock->name,
                $stock->sku,
                $stock->quantity,
                $stock->unit,
                $stock->price,
                $stock->min_stock,
                $stock->status->value,
            ], null, 'A'.$row);
            $row++;
        }

        return $this->toBinary($spreadsheet);
    }

    /** Template kosong berisi header + satu contoh baris. */
    public function template(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stok');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['Contoh Barang', 'SKU-001', 10, 'pcs', 15000, 5, 'available'], null, 'A2');

        return $this->toBinary($spreadsheet);
    }

    /** Import baris 2..n. Kolom status diabaikan (dihitung ulang dari qty & min_stock). */
    public function import(User $user, string $path): int
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $count = 0;

        foreach ($sheet->toArray(null, true, true, false) as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));
            if ($name === '') {
                continue;
            }

            $this->stockService->create($user, [
                'name'      => $name,
                'sku'       => $row[1] ?? null,
                'quantity'  => (int) ($row[2] ?? 0),
                'unit'      => $row[3] ?? 'pcs',
                'price'     => is_numeric($row[4] ?? null) ? $row[4] : null,
                'min_stock' => is_numeric($row[5] ?? null) ? (int) $row[5] : 5,
            ]);
            $count++;
        }

        return $count;
    }

    private function toBinary(Spreadsheet $spreadsheet): string
    {
        $tmp = tempnam(storage_path('framework/cache'), 'stk');
        (new Xlsx($spreadsheet))->save($tmp);
        $binary = (string) file_get_contents($tmp);
        unlink($tmp);

        return $binary;
    }
}