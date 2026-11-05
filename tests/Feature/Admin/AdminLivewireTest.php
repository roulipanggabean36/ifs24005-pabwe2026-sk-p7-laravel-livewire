<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_user_list_shows_members_with_stock_count(): void
    {
        $member = User::factory()->member()->create(['name' => 'Siti Member']);
        Stock::factory()->count(3)->create(['user_id' => $member->id]);

        Livewire::actingAs($this->admin)->test(UserList::class)
            ->assertSee('Siti Member')
            ->assertSee('3');
    }

    public function test_user_list_search(): void
    {
        User::factory()->member()->create(['name' => 'Siti Member']);
        User::factory()->member()->create(['name' => 'Joko Lain']);

        Livewire::actingAs($this->admin)->test(UserList::class)
            ->set('search', 'Siti')
            ->assertSee('Siti Member')->assertDontSee('Joko Lain');
    }

    public function test_user_stock_list_is_read_only(): void
    {
        $member = User::factory()->member()->create();
        Stock::factory()->withQuantity(50)->create(['user_id' => $member->id, 'name' => 'Kabel', 'sku' => 'KB-1']);
        Stock::factory()->withQuantity(0)->create(['user_id' => $member->id, 'name' => 'Lampu', 'sku' => 'LP-1']);

        Livewire::actingAs($this->admin)->test(AdminUserStockList::class, ['user' => $member])
            ->assertSee('Kabel')->assertSee('Lampu')
            ->assertDontSee('Hapus')->assertDontSee('Edit')
            ->set('search', 'Kabel')->assertDontSee('Lampu')
            ->set('search', '')
            ->set('statusFilter', 'out_of_stock')->assertSee('Lampu')->assertDontSee('Kabel');
    }
}