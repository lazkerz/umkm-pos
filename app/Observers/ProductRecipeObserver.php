<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductRecipe;
use App\Support\StoreCache;

class ProductRecipeObserver
{
    public function saved(ProductRecipe $recipe): void
    {
        $this->forget($recipe);
    }

    public function deleted(ProductRecipe $recipe): void
    {
        $this->forget($recipe);
    }

    private function forget(ProductRecipe $recipe): void
    {
        // Recipes are eager-loaded as part of the cached product payload.
        $storeId = $recipe->product?->store_id
            ?? Product::find($recipe->product_id)?->store_id;

        if ($storeId) {
            StoreCache::forgetProducts($storeId);
        }
    }
}
