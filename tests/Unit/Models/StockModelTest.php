<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_and_relation(): void
    {
        $stock = Stock::factory()->withQuantity(0)->create();

        $this->assertInstanceOf(StockStatus::class, $stock->status);
        $this->assertSame(StockStatus::OutOfStock, $stock->status);
        $this->assertIsInt($stock->quantity);
        $this->assertInstanceOf(User::class, $stock->user);
    }
}