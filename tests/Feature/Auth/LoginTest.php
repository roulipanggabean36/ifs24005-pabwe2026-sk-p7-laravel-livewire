<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_required_ids(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="login-username"', false)
            ->assertSee('id="login-password"', false)
            ->assertSee('id="login-submit"', false);
    }

    public function test_login_with_username(): void
    {
        $user = User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'rahasia')
            ->call('submit')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_email(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia']);

        Livewire::test(Login::class)
            ->set('login', 'budi@example.com')
            ->set('password', 'rahasia')
            ->call('submit')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_shows_error(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        Livewire::test(Login::class)
            ->set('login', 'budi')
            ->set('password', 'salah')
            ->call('submit')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_fields_are_required(): void
    {
        Livewire::test(Login::class)
            ->call('submit')
            ->assertHasErrors(['login', 'password']);
    }

    public function test_logged_in_user_is_redirected_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('home'));
    }
}