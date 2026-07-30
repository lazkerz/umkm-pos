<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Centralizes cache keys for per-store reference data (products, categories,
 * units, promotions) so read-heavy pages like the kasir screen don't hit the
 * database on every request. Invalidated by the observers in App\Observers.
 */
class StoreCache
{
    public static function products(int $storeId): Collection
    {
        return Cache::rememberForever(
            "store:{$storeId}:products:available",
            fn () => Product::where('store_id', $storeId)
                ->available()
                ->with(['category', 'recipes'])
                ->get()
        );
    }

    public static function forgetProducts(int $storeId): void
    {
        Cache::forget("store:{$storeId}:products:available");
    }

    public static function categories(int $storeId): Collection
    {
        return Cache::rememberForever(
            "store:{$storeId}:categories",
            fn () => Category::where('store_id', $storeId)->get()
        );
    }

    public static function forgetCategories(int $storeId): void
    {
        Cache::forget("store:{$storeId}:categories");
    }

    public static function unitsAvailableFor(int $storeId): Collection
    {
        $global = Cache::rememberForever(
            'units:global',
            fn () => Unit::whereNull('store_id')->get()
        );

        $custom = Cache::rememberForever(
            "store:{$storeId}:units:custom",
            fn () => Unit::where('store_id', $storeId)->get()
        );

        return $global->merge($custom);
    }

    public static function forgetUnits(?int $storeId): void
    {
        if ($storeId === null) {
            Cache::forget('units:global');

            return;
        }

        Cache::forget("store:{$storeId}:units:custom");
    }

    public static function activePromotions(int $storeId): Collection
    {
        return Cache::remember(
            "store:{$storeId}:promotions:active",
            now()->addMinutes(10),
            fn () => Promotion::where('store_id', $storeId)->get()
        );
    }

    public static function forgetPromotions(int $storeId): void
    {
        Cache::forget("store:{$storeId}:promotions:active");
    }
}
