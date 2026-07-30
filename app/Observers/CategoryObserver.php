<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\StoreCache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        StoreCache::forgetCategories($category->store_id);
    }

    public function deleted(Category $category): void
    {
        StoreCache::forgetCategories($category->store_id);
    }
}
