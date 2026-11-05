<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_users_only_members_with_counts(): void
    {
        $service = new AdminUserService(new StockRepository());
        User::factory()->admin()->create(['name' => 'Admin']);
        $member = User::factory()->member()->create(['name' => 'Siti', 'username' => 'siti', 'email' => 's@example.com']);
        User::factory()->member()->create(['name' => 'Joko']);
        Stock::factory()->count(2)->create(['user_id' => $member->id]);

        $all = $service->paginateUsers();
        $this->assertSame(2, $all->total());

        $found = $service->paginateUsers('siti');
        $this->assertSame(1, $found->total());
        $this->assertSame(2, $found->first()->stocks_count);
    }

    public function test_paginate_stocks_of_user(): void
    {
        $service = new AdminUserService(new StockRepository());
        $member  = User::factory()->member()->create();
        Stock::factory()->withQuantity(0)->create(['user_id' => $member->id]);
        Stock::factory()->withQuantity(50)->create(['user_id' => $member->id]);

        $this->assertSame(2, $service->paginateStocksOf($member)->total());
        $this->assertSame(1, $service->paginateStocksOf($member, null, 'out_of_stock')->total());
    }
}