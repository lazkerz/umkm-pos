<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Support\StoreCache;

class PromotionObserver
{
    public function saved(Promotion $promotion): void
    {
        StoreCache::forgetPromotions($promotion->store_id);
    }

    public function deleted(Promotion $promotion): void
    {
        StoreCache::forgetPromotions($promotion->store_id);
    }
}
