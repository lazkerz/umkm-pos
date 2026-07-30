<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\StoreCache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        StoreCache::forgetProducts($product->store_id);
    }

    public function deleted(Product $product): void
    {
        StoreCache::forgetProducts($product->store_id);
    }
}
