<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'unit_id',
        'quantity',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
        ];
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function distributions()
    {
        return $this->hasMany(StockDistribution::class);
    }

    
    public function recipeUsages()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }
}
