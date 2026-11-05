<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_from_array_full(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => ' Lem ', 'sku' => 'L-1', 'description' => 'd', 'quantity' => '10',
            'unit' => 'box', 'price' => '5000', 'min_stock' => 2,
        ]);

        $this->assertSame('Lem', $item->name);
        $this->assertSame(10, $item->quantity);
        $this->assertSame(5000.0, $item->price);
        $this->assertSame(
            ['name' => 'Lem', 'sku' => 'L-1', 'description' => 'd', 'quantity' => 10,
             'unit' => 'box', 'price' => 5000.0, 'min_stock' => 2],
            $item->toArray()
        );
    }

    public function test_from_array_defaults(): void
    {
        $item = GeneratedStockItem::fromArray(['quantity' => -3]);

        $this->assertSame('', $item->name);
        $this->assertNull($item->sku);
        $this->assertNull($item->description);
        $this->assertSame(0, $item->quantity);
        $this->assertSame('pcs', $item->unit);
        $this->assertNull($item->price);
        $this->assertSame(5, $item->minStock);
    }
}