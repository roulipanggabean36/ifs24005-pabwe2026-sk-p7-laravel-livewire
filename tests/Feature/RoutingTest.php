<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_admin_root_redirects_to_users(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertRedirect(route('admin.users'));
    }

    public function test_member_root_redirects_to_stocks(): void
    {
        $this->actingAs(User::factory()->member()->create())
            ->get('/')->assertRedirect(route('stocks.index'));
    }

    public function test_guest_cannot_open_stocks(): void
    {
        $this->get('/stocks')->assertRedirect(route('login'));
    }

    public function test_admin_cannot_open_stocks(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/stocks')->assertForbidden();
    }

    public function test_member_cannot_open_admin_pages(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get("/admin/users/{$member->id}/stocks")->assertForbidden();
    }

    public function test_member_can_open_own_stock_but_not_others(): void
    {
        $member = User::factory()->member()->create();
        $own    = Stock::factory()->create(['user_id' => $member->id]);
        $other  = Stock::factory()->create();

        $this->actingAs($member)->get(route('stocks.show', $own))->assertOk();
        $this->actingAs($member)->get(route('stocks.show', $other))->assertForbidden();
    }

    public function test_admin_pages_open_for_admin(): void
    {
        $admin  = User::factory()->admin()->create();
        $member = User::factory()->member()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get("/admin/users/{$member->id}/stocks")->assertOk();
    }

    public function test_profile_requires_login(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/profile')->assertOk();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}