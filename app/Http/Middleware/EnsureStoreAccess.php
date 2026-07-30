<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    /**
     * Pastikan user hanya bisa akses data toko yang berhak dia akses:
     * - Owner: hanya toko yang dia miliki (store.owner_id === user.id)
     * - Staff: hanya toko tempat dia ditugaskan (user.store_id === store.id)
     *
     * Route harus punya parameter {store} (route model binding).
     * Contoh route: Route::get('/stores/{store}/products', ...)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $store = $request->route('store');

        
        if ($store && ! $store instanceof Store) {
            $store = Store::findOrFail($store);
        }

        if (! $store) {
            abort(404, 'Toko tidak ditemukan.');
        }

        $hasAccess = $user->isOwner()
            ? $store->owner_id === $user->id
            : $user->store_id === $store->id;

        if (! $hasAccess) {
            abort(403, 'Kamu tidak punya akses ke toko ini.');
        }

        return $next($request);
    }
}
