<?php

namespace App\Http\Middleware;

use App\Features\Stock\Models\Stock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStockOwnership
{
    /** Jika rute memuat {stock}, pastikan stok itu milik user yang login. */
    public function handle(Request $request, Closure $next): Response
    {
        $stock = $request->route('stock');

        if ($stock instanceof Stock && $stock->user_id !== $request->user()?->id) {
            abort(403, 'Stok ini bukan milik Anda.');
        }

        return $next($request);
    }
}