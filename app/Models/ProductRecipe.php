<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'stock_item_id', 'quantity_needed'];

    protected function casts(): array
    {
        return [
            'quantity_needed' => 'decimal:3',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
