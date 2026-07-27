<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Hanya user dengan role 'owner' yang boleh lewat.
     * Dipakai untuk: create toko, distribusi stok, approve/reject pengeluaran,
     * dan dashboard agregat semua toko.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isOwner()) {
            abort(403, 'Hanya Owner yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}
