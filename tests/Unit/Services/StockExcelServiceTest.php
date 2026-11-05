<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockExcelService $excel;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->excel = new StockExcelService(new StockService(new StockRepository()));
        $this->user  = User::factory()->create();
    }

    private function writeTemp(string $binary): string
    {
        $path = tempnam(storage_path('framework/cache'), 'tst');
        file_put_contents($path, $binary);

        return $path;
    }

    public function test_export_contains_headers_and_rows(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->user->id, 'name' => 'Kabel']);

        $binary = $this->excel->export(collect([$stock]));
        $path   = $this->writeTemp($binary);
        $rows   = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertSame(StockExcelService::HEADERS, $rows[0]);
        $this->assertSame('Kabel', $rows[1][0]);
    }

    public function test_template_has_example_row(): void
    {
        $path = $this->writeTemp($this->excel->template());
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertSame('Contoh Barang', $rows[1][0]);
    }

    public function test_import_skips_blank_names_and_handles_non_numeric(): void
    {
        $sheet = new Spreadsheet();
        $ws    = $sheet->getActiveSheet();
        $ws->fromArray(StockExcelService::HEADERS, null, 'A1');
        $ws->fromArray(['Kabel', 'K-1', 3, 'pcs', 1000, 5, 'x'], null, 'A2');
        $ws->fromArray(['', 'K-2', 3, 'pcs', 1000, 5, 'x'], null, 'A3');
        $ws->fromArray(['Lampu', null, 10, null, 'abc', 'abc', null], null, 'A4');

        $path = tempnam(storage_path('framework/cache'), 'tst');
        (new Xlsx($sheet))->save($path);

        $count = $this->excel->import($this->user, $path);
        unlink($path);

        $this->assertSame(2, $count);
        $this->assertSame('low_stock', Stock::where('name', 'Kabel')->first()->status->value);

        $lampu = Stock::where('name', 'Lampu')->first();
        $this->assertNull($lampu->price);
        $this->assertSame(5, $lampu->min_stock);
        $this->assertSame('pcs', $lampu->unit);
    }
}