<?php

namespace App\Observers;

use App\Models\Unit;
use App\Support\StoreCache;

class UnitObserver
{
    public function saved(Unit $unit): void
    {
        StoreCache::forgetUnits($unit->store_id);
    }

    public function deleted(Unit $unit): void
    {
        StoreCache::forgetUnits($unit->store_id);
    }
}
