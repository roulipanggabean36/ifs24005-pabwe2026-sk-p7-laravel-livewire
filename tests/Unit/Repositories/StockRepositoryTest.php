<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockRepository $repo;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new StockRepository();
        $this->user = User::factory()->create();
    }

    public function test_paginate_with_search_and_status(): void
    {
        Stock::factory()->withQuantity(50)->create(['user_id' => $this->user->id, 'name' => 'Kabel', 'sku' => 'KB-1']);
        Stock::factory()->withQuantity(0)->create(['user_id' => $this->user->id, 'name' => 'Lampu', 'sku' => 'LP-1']);
        Stock::factory()->create(['name' => 'Orang Lain']);

        $this->assertSame(2, $this->repo->paginateForUser($this->user->id)->total());
        $this->assertSame(1, $this->repo->paginateForUser($this->user->id, 'Kabel')->total());
        $this->assertSame(1, $this->repo->paginateForUser($this->user->id, 'LP-1')->total());
        $this->assertSame(1, $this->repo->paginateForUser($this->user->id, null, 'out_of_stock')->total());
    }

    public function test_all_find_create_update_delete(): void
    {
        $stock = $this->repo->create(['user_id' => $this->user->id, 'name' => 'B', 'quantity' => 1]);
        $this->repo->create(['user_id' => $this->user->id, 'name' => 'A', 'quantity' => 1]);

        $this->assertSame(['A', 'B'], $this->repo->allForUser($this->user->id)->pluck('name')->all());
        $this->assertSame($stock->id, $this->repo->find($stock->id)->id);
        $this->assertNull($this->repo->find(999999));

        $updated = $this->repo->update($stock, ['name' => 'C']);
        $this->assertSame('C', $updated->name);

        $this->assertTrue($this->repo->delete($stock));
        $this->assertNull($this->repo->find($stock->id));
    }
}