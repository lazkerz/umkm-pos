<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'name', 'symbol'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    
    public function scopeAvailableFor($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->whereNull('store_id')->orWhere('store_id', $storeId);
        });
    }

    public function isCustom(): bool
    {
        return $this->store_id !== null;
    }
}
