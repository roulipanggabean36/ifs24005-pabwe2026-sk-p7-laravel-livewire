<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    private function dispatch(?User $user, mixed $routeStock)
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => new class($routeStock) {
            public function __construct(private mixed $stock) {}

            public function parameter(string $name, mixed $default = null): mixed
            {
                return $this->stock ?? $default;
            }
        });

        return (new EnsureStockOwnership())->handle($request, fn () => new Response('ok'));
    }

    public function test_passes_without_stock_parameter(): void
    {
        $this->assertSame('ok', $this->dispatch(new User(), null)->getContent());
    }

    public function test_passes_for_owner(): void
    {
        $user = new User();
        $user->id = 7;
        $stock = new Stock(['user_id' => 7]);

        $this->assertSame('ok', $this->dispatch($user, $stock)->getContent());
    }

    public function test_blocks_non_owner(): void
    {
        $user = new User();
        $user->id = 8;

        $this->expectException(HttpException::class);
        $this->dispatch($user, new Stock(['user_id' => 7]));
    }
}