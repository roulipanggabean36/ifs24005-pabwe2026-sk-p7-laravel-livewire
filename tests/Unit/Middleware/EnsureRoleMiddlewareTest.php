<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    private function dispatch(?User $user, string ...$roles)
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);

        return (new EnsureRole())->handle($request, fn () => new Response('ok'), ...$roles);
    }

    public function test_allows_matching_role(): void
    {
        $this->assertSame('ok', $this->dispatch(new User(['role' => 'admin']), 'admin')->getContent());
    }

    public function test_blocks_wrong_role(): void
    {
        $this->expectException(HttpException::class);
        $this->dispatch(new User(['role' => 'member']), 'admin');
    }

    public function test_blocks_guest(): void
    {
        $this->expectException(HttpException::class);
        $this->dispatch(null, 'admin');
    }
}