<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService(new StockRepository());
        $this->user    = User::factory()->create();
    }

    public function test_create_normalizes_input(): void
    {
        $stock = $this->service->create($this->user, [
            'name' => ' Kabel ', 'sku' => '', 'description' => '', 'quantity' => -4,
            'unit' => '', 'price' => '', 'min_stock' => 5,
        ]);

        $this->assertSame('Kabel', $stock->name);
        $this->assertNull($stock->sku);
        $this->assertNull($stock->description);
        $this->assertSame(0, $stock->quantity);
        $this->assertSame('pcs', $stock->unit);
        $this->assertNull($stock->price);
        $this->assertSame('out_of_stock', $stock->status->value);
        $this->assertSame($this->user->id, $stock->user_id);
    }

    public function test_create_with_values_and_update(): void
    {
        $stock = $this->service->create($this->user, [
            'name' => 'Lem', 'sku' => 'L-1', 'description' => 'd', 'quantity' => 20,
            'unit' => 'box', 'price' => 1500, 'min_stock' => 5,
        ]);
        $this->assertSame('available', $stock->status->value);

        $updated = $this->service->update($stock, ['name' => 'Lem', 'quantity' => 3, 'min_stock' => 5]);
        $this->assertSame('low_stock', $updated->status->value);
    }

    public function test_paginate(): void
    {
        Stock::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->assertSame(3, $this->service->paginate($this->user)->total());
    }

    public function test_adjust_quantity(): void
    {
        $stock = Stock::factory()->withQuantity(6, 5)->create(['user_id' => $this->user->id]);

        $this->assertSame('low_stock', $this->service->adjustQuantity($stock, -1)->status->value);
        $this->assertSame(0, $this->service->adjustQuantity($stock, -100)->quantity);
    }

    public function test_cover_lifecycle_and_delete(): void
    {
        Storage::fake('public');
        $stock = Stock::factory()->create(['user_id' => $this->user->id]);

        $stock = $this->service->setCover($stock, UploadedFile::fake()->image('a.jpg'));
        $first = $stock->cover_image_path;
        Storage::disk('public')->assertExists($first);

        $stock = $this->service->setCover($stock, UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);

        $stock = $this->service->removeCover($stock);
        $this->assertNull($stock->cover_image_path);

        $stock = $this->service->setCover($stock, UploadedFile::fake()->image('c.jpg'));
        $path  = $stock->cover_image_path;
        $this->assertTrue($this->service->delete($stock));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_create_many(): void
    {
        $count = $this->service->createMany($this->user, [
            new GeneratedStockItem('A'),
            new GeneratedStockItem('B', 'B-1', 'd', 3, 'pcs', 100.0, 5),
        ]);

        $this->assertSame(2, $count);
        $this->assertSame(2, Stock::count());
    }
}