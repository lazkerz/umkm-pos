<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;

/**
 * Centralizes cache keys for per-store reference data (products, categories,
 * units, promotions) so read-heavy pages like the kasir screen don't hit the
 * database on every request. Invalidated by the observers in App\Observers.
 *
 * Cached values are always plain arrays (never Eloquent models/collections):
 * the `database` cache driver stores values via PHP serialize()/unserialize(),
 * and serialized Eloquent objects are fragile across code/class changes
 * (can come back as __PHP_Incomplete_Class). Plain scalars/arrays sidestep
 * that entirely.
 */
class StoreCache
{
    public static function products(int $storeId): array
    {
        return Cache::rememberForever(
            "store:{$storeId}:products:available",
            fn () => Product::where('store_id', $storeId)
                ->available()
                ->with(['category', 'recipes'])
                ->get()
                ->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'recipes' => $product->recipes->map(fn ($recipe) => [
                        'stock_item_id' => $recipe->stock_item_id,
                        'quantity_needed' => (float) $recipe->quantity_needed,
                    ])->values()->all(),
                ])
                ->values()
                ->all()
        );
    }

    public static function forgetProducts(int $storeId): void
    {
        Cache::forget("store:{$storeId}:products:available");
    }

    public static function categories(int $storeId): array
    {
        return Cache::rememberForever(
            "store:{$storeId}:categories",
            fn () => Category::where('store_id', $storeId)
                ->get()
                ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])
                ->values()
                ->all()
        );
    }

    public static function forgetCategories(int $storeId): void
    {
        Cache::forget("store:{$storeId}:categories");
    }

    public static function unitsAvailableFor(int $storeId): array
    {
        $global = Cache::rememberForever(
            'units:global',
            fn () => Unit::whereNull('store_id')
                ->get()
                ->map(fn ($unit) => ['id' => $unit->id, 'name' => $unit->name, 'symbol' => $unit->symbol])
                ->values()
                ->all()
        );

        $custom = Cache::rememberForever(
            "store:{$storeId}:units:custom",
            fn () => Unit::where('store_id', $storeId)
                ->get()
                ->map(fn ($unit) => ['id' => $unit->id, 'name' => $unit->name, 'symbol' => $unit->symbol])
                ->values()
                ->all()
        );

        return [...$global, ...$custom];
    }

    public static function forgetUnits(?int $storeId): void
    {
        if ($storeId === null) {
            Cache::forget('units:global');

            return;
        }

        Cache::forget("store:{$storeId}:units:custom");
    }

    public static function activePromotions(int $storeId): array
    {
        return Cache::remember(
            "store:{$storeId}:promotions:active",
            now()->addMinutes(10),
            fn () => Promotion::where('store_id', $storeId)
                ->get()
                ->map(fn ($promotion) => [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'type' => $promotion->type,
                    'value' => (float) $promotion->value,
                    'channel' => $promotion->channel,
                    'start_date' => $promotion->start_date->toDateString(),
                    'end_date' => $promotion->end_date->toDateString(),
                    'is_active' => $promotion->is_active,
                ])
                ->values()
                ->all()
        );
    }

    public static function forgetPromotions(int $storeId): void
    {
        Cache::forget("store:{$storeId}:promotions:active");
    }
}
